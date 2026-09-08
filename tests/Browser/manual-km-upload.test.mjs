// Real Chrome -> multipart HTTP -> Laravel -> isolated SQLite/private disk.
// Start manual-km-server.php in a disposable container, then run:
// MANUAL_KM_FIXTURES_DIR=<valid docx/pptx directory> node --test tests/Browser/manual-km-upload.test.mjs
// Optional MANUAL_KM_TEST_ORIGIN (default http://127.0.0.1:18081), CHROME_BIN.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { spawn } from 'node:child_process';
import { mkdtemp, readFile, readdir, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { basename, extname, resolve, join, sep } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

test('both Manual KM roles create and replace real Office attachments through Chrome', async (t) => {
    assert.ok(process.env.MANUAL_KM_FIXTURES_DIR, 'MANUAL_KM_FIXTURES_DIR must point to valid Office fixtures');
    const directory = resolve(process.env.MANUAL_KM_FIXTURES_DIR);
    const fixtures = (await readdir(directory)).filter((name) => /\.(docx|pptx)$/i.test(name)).sort().map((name) => join(directory, name));
    for (const extension of ['.docx', '.pptx']) assert.ok(fixtures.some((file) => extname(file).toLowerCase() === extension), `${extension} fixture exists`);
    const origin = process.env.MANUAL_KM_TEST_ORIGIN || 'http://127.0.0.1:18081';
    const expectedError = process.env.MANUAL_KM_EXPECT_ERROR;
    assert.ok(['127.0.0.1', 'localhost'].includes(new URL(origin).hostname), 'only a local isolated server is allowed');
    const status = async () => {
        const response = await fetch(origin + '/__manual-km-test/status');
        assert.equal(response.status, 200);
        const value = await response.json();
        assert.equal(value.isolated, true, 'server confirms test-only state');
        assert.deepEqual(value.public_files, [], 'no uploaded files on the public disk');
        return value;
    };
    assert.deepEqual((await status()).items, [], 'use a fresh isolated server for every run');

    const profile = await mkdtemp(join(tmpdir(), 'manual-km-browser-chrome-'));
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
        const requests = [];
        const redirects = [];
        socket.addEventListener('message', ({ data }) => {
            const response = JSON.parse(data);
            if (response.method === 'Network.requestWillBeSent') {
                requests.push(response.params.request);
                if (response.params.redirectResponse) redirects.push(response.params.redirectResponse);
            }
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
            for (let attempt = 0; attempt < 150; attempt++) {
                try { if (await evaluate(expression)) return; } catch { /* navigation can replace the execution context */ }
                await delay(100);
            }
            assert.fail(`Browser condition timed out: ${expression}\n${await evaluate('document.body.innerText.slice(-2000)')}`);
        };
        const navigate = async (path) => {
            await call('Page.navigate', { url: origin + path });
            await until(`document.readyState === 'complete' && location.pathname === ${JSON.stringify(path)}`);
        };
        await call('Network.enable');
        await call('Page.enable');
        for (const [prefix, role] of [['superadmin', 'Super Admin'], ['competition-admin', 'Competition Admin']]) {
            await call('Network.clearBrowserCookies');
            await navigate('/login');
            await evaluate(`document.querySelector('[name=email]').value = ${JSON.stringify(prefix + '@browser.example')}; document.querySelector('[name=password]').value = 'manual-km-browser-password'; document.querySelector('form[action$="/login"] button[type=submit]').click()`);
            await until("document.readyState === 'complete' && location.pathname !== '/login'");

            for (const fixture of fixtures) {
                await t.test(`${role}: ${basename(fixture)} ${expectedError ? 'reject' : 'create and replace'}`, async () => {
                    const title = `${prefix}: ${basename(fixture)}`;
                    const hash = createHash('sha256').update(await readFile(fixture)).digest('hex');
                    let item;
                    for (const editing of (expectedError ? [false] : [false, true])) {
                        await navigate(editing ? `/${prefix}/km/${item.id}/edit` : `/${prefix}/km/create`);
                        const input = await evaluate(`({ accept: document.querySelector('#attachment').accept, enctype: document.querySelector('#attachment').form.enctype })`);
                        assert.equal(input.enctype, 'multipart/form-data');
                        for (const extension of ['.docx', '.pptx']) assert.ok(input.accept.split(',').includes(extension));
                        await evaluate(`document.querySelector('#title').value = ${JSON.stringify(title + (editing ? ' updated' : ''))}; document.querySelector('#category_id').selectedIndex = 1`);
                        const document = await call('DOM.getDocument');
                        const inputNode = await call('DOM.querySelector', { nodeId: document.root.nodeId, selector: '#attachment' });
                        await call('DOM.setFileInputFiles', { nodeId: inputNode.nodeId, files: [fixture] });
                        assert.equal(await evaluate("document.querySelector('#attachment').files[0].name"), basename(fixture));
                        const requestStart = requests.length;
                        const redirectStart = redirects.length;
                        await evaluate("document.querySelector('#attachment').form.querySelector('button:not([type=button])').click()");
                        if (expectedError) {
                            await until(`document.readyState === 'complete' && location.pathname === '/${prefix}/km/create' && document.body.innerText.includes(${JSON.stringify(expectedError)})`);
                            const rejected = requests.slice(requestStart).find((request) => request.method === 'POST');
                            assert.ok(rejected, 'Chrome sent the rejected upload to Laravel');
                            const redirect = redirects.slice(redirectStart).find((response) => response.url === rejected.url && response.status === 302);
                            assert.ok(redirect, 'validation returned HTTP 302');
                            const snapshot = await status();
                            assert.deepEqual(snapshot.items, [], 'rejected upload creates no item');
                            assert.deepEqual(snapshot.private_files, [], 'rejected upload stores no files');
                            continue;
                        }
                        await until(`document.readyState === 'complete' && /^\\/${prefix}\\/km\\/\\d+$/.test(location.pathname)`);
                        const outgoing = requests.slice(requestStart).find((request) => request.method === 'POST' && new URL(request.url).pathname.startsWith(`/${prefix}/km`));
                        assert.ok(outgoing, 'Chrome sent a POST request to Laravel');
                        const contentType = Object.entries(outgoing.headers).find(([key]) => key.toLowerCase() === 'content-type')?.[1];
                        assert.match(contentType, /^multipart\/form-data; boundary=/, 'real browser multipart encoding');
                        const redirect = redirects.slice(redirectStart).find((response) => response.url === outgoing.url && response.status === 302);
                        assert.ok(redirect, 'Laravel returned its normal success redirect');
                        const snapshot = await status();
                        const current = snapshot.items.find((candidate) => candidate.title === title + (editing ? ' updated' : ''));
                        assert.ok(current, 'controller persisted the Manual KM item');
                        assert.equal(current.role, role);
                        assert.equal(current.submission_id, null);
                        assert.equal(current.name, basename(fixture));
                        assert.equal(current.sha256, hash, 'private attachment bytes match the selected Office file');
                        const destination = Object.entries(redirect.headers).find(([key]) => key.toLowerCase() === 'location')?.[1];
                        assert.equal(new URL(destination, origin).pathname, `/${prefix}/km/${current.id}`, '302 Location points to the saved item');
                        assert.equal(await evaluate('location.pathname'), `/${prefix}/km/${current.id}`, 'Chrome followed the success redirect');
                        if (editing) {
                            assert.notEqual(current.path, item.path, 'replacement gets a new private path');
                            assert.ok(!snapshot.private_files.includes(item.path), 'old private file was removed');
                        }
                        item = current;
                    }
                });
            }
        }
        await call('Browser.close');
    } finally {
        socket?.close();
        if (chrome.exitCode === null) {
            await Promise.race([new Promise((done) => chrome.once('exit', done)), delay(3000)]);
            if (chrome.exitCode === null) chrome.kill();
        }
        assert.ok(resolve(profile).startsWith(resolve(tmpdir()) + sep + 'manual-km-browser-chrome-'));
        await rm(profile, { recursive: true, force: true, maxRetries: 10, retryDelay: 200 });
    }
});
