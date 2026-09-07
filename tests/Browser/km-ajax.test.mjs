// Real Chrome DOM/fetch/redirect regression using an isolated HTTP fixture.
// Backend routing and Blade wiring are covered by KnowledgeItemAjaxTest.
// Run: node --test tests/Browser/km-ajax.test.mjs (Chrome or CHROME_BIN required).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { spawn } from 'node:child_process';
import { mkdtemp, readFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { resolve, join, sep } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

test('KM AJAX updates the real DOM without reloading the current page', async (t) => {
    const script = await readFile(new URL('../../resources/js/ajax-form.js', import.meta.url), 'utf8');
    let status = 'draft';
    let exists = true;
    const html = (detail) => {
        const target = detail ? '#km-detail' : '#km-list, #km-total';
        const action = status === 'published' ? 'unpublish' : 'publish';
        const form = (name, attributes) => `<form method="POST" action="/${name}?detail=${detail}" data-ajax-form ${attributes}><button type="submit">${name}</button></form>`;
        return `<!doctype html><html><body><p id="km-total">${Number(exists)}</p>
            <main id="${detail ? 'km-detail' : 'km-list'}">${exists ? `<span id="badge">${status}</span>
            ${form(action, `data-ajax-target="${target}"`)}
            ${form('delete', detail ? 'data-ajax-redirect="/"' : `data-ajax-target="${target}"`)}` : 'empty'}</main>
            <script>window.pageMarker = Math.random();</script><script src="/ajax-form.js"></script></body></html>`;
    };
    const server = createServer((req, res) => {
        const url = new URL(req.url, 'http://fixture');
        if (url.pathname === '/ajax-form.js') {
            res.writeHead(200, { 'Content-Type': 'text/javascript' }).end(script);
        } else if (req.method === 'POST') {
            if (url.pathname === '/delete') exists = false;
            else status = url.pathname === '/publish' ? 'published' : 'draft';
            res.writeHead(302, { Location: url.pathname === '/delete' ? '/' : (url.searchParams.get('detail') === 'true' ? '/detail' : '/') }).end();
        } else {
            res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' }).end(html(url.pathname === '/detail'));
        }
    });
    await new Promise((done) => server.listen(0, '127.0.0.1', done));
    const origin = `http://127.0.0.1:${server.address().port}`;
    const profile = await mkdtemp(join(tmpdir(), 'km-ajax-chrome-'));
    const chrome = spawn(process.env.CHROME_BIN || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', [
        '--headless=new', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank',
    ], { windowsHide: true, stdio: 'ignore' });
    let socket;
    try {
        let port;
        for (let attempt = 0; attempt < 100 && !port; attempt++) {
            try { port = (await readFile(join(profile, 'DevToolsActivePort'), 'utf8')).split('\n')[0]; } catch { await delay(100); }
        }
        assert.ok(port, 'Chrome debugging endpoint started');
        const pages = await (await fetch(`http://127.0.0.1:${port}/json`)).json();
        socket = new WebSocket(pages.find((page) => page.type === 'page').webSocketDebuggerUrl);
        await new Promise((done) => socket.addEventListener('open', done, { once: true }));
        let sequence = 0;
        const pending = new Map();
        socket.addEventListener('message', ({ data }) => {
            const response = JSON.parse(data);
            if (pending.has(response.id)) {
                const { done, reject } = pending.get(response.id);
                pending.delete(response.id);
                response.error ? reject(new Error(response.error.message)) : done(response.result);
            }
        });
        const call = (method, params = {}) => new Promise((done, reject) => {
            const id = ++sequence;
            pending.set(id, { done, reject });
            socket.send(JSON.stringify({ id, method, params }));
        });
        const evaluate = async (expression) => {
            const response = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
            assert.equal(response.exceptionDetails, undefined, 'browser script did not throw');
            return response.result.value;
        };
        const until = async (expression) => {
            for (let attempt = 0; attempt < 100; attempt++) {
                if (await evaluate(expression)) return;
                await delay(50);
            }
            assert.fail(`Browser condition timed out: ${expression}`);
        };
        const navigate = async (path) => {
            await call('Page.navigate', { url: origin + path });
            await until(`document.readyState === 'complete' && location.pathname === '${path}' && !!window.pageMarker`);
        };
        const submit = (action) => evaluate(`document.querySelector('form[action^="/${action}?"]').requestSubmit()`);
        for (const detail of [false, true]) {
            status = 'draft'; exists = true;
            await navigate(detail ? '/detail' : '/');
            const marker = await evaluate('window.pageMarker');
            await t.test(`${detail ? 'detail' : 'list'} publish changes badge and action`, async () => {
                await submit('publish');
                await until(`document.querySelector('#badge')?.textContent === 'published' && !!document.querySelector('form[action^="/unpublish?"]')`);
                assert.equal(await evaluate('window.pageMarker'), marker);
            });
            await t.test(`${detail ? 'detail' : 'list'} unpublish changes badge and action`, async () => {
                await submit('unpublish');
                await until(`document.querySelector('#badge')?.textContent === 'draft' && !!document.querySelector('form[action^="/publish?"]')`);
                assert.equal(await evaluate('window.pageMarker'), marker);
            });
            await t.test(`${detail ? 'detail' : 'list'} delete ${detail ? 'returns to list' : 'removes card and updates total'}`, async () => {
                await submit('delete');
                await until(`!!document.querySelector('#km-list') && !document.querySelector('#badge') && document.querySelector('#km-total')?.textContent === '0'`);
                if (detail) assert.equal(await evaluate('location.pathname'), '/');
                else assert.equal(await evaluate('window.pageMarker'), marker);
            });
        }
        await call('Browser.close');
    } finally {
        socket?.close();
        if (chrome.exitCode === null) {
            await Promise.race([new Promise((done) => chrome.once('exit', done)), delay(3000)]);
            if (chrome.exitCode === null) chrome.kill();
        }
        await new Promise((done) => server.close(done));
        // Delete only this test's generated temporary profile, never a user profile.
        assert.ok(resolve(profile).startsWith(resolve(tmpdir()) + sep + 'km-ajax-chrome-'));
        await rm(profile, { recursive: true, force: true, maxRetries: 10, retryDelay: 200 });
    }
});
