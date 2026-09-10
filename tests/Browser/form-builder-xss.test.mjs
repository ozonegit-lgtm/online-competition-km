// Real Chrome DOM test of the inline scripts/templates; only Blade server values are fixture data.
// Run: node --test tests/Browser/form-builder-xss.test.mjs (Chrome or CHROME_BIN required).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { mkdtemp, readFile, writeFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join, resolve, sep } from 'node:path';
import { pathToFileURL } from 'node:url';

const run = promisify(execFile);
const payloads = [
    '"><img src=x onerror=alert(1)>',
    '"><svg onload=alert(1)>',
    '<script>alert(1)</script>',
    '\'"><script>alert(1)</script>',
    'x" onfocus="alert(1)" data-injected="',
];
const scriptJson = value => JSON.stringify(value).replace(/</g, '\\u003c');

function exercise(mode, payloads) {
    const check = (condition, message) => { if (!condition) throw new Error(message); };
    const cards = () => [...document.querySelectorAll('.field-card')];
    const first = () => cards()[0];
    const options = () => [...first().querySelectorAll('.option-input')];
    const change = (element, value, type = 'input') => {
        element.value = value;
        element.dispatchEvent(new Event(type, { bubbles: true }));
    };
    const assertOptions = expected => {
        check(JSON.stringify(options().map(input => input.value)) === JSON.stringify(expected), 'option values changed');
        check(!document.querySelector('#fieldsContainer img, #fieldsContainer svg, #fieldsContainer script'), 'payload became markup');
        check([...document.querySelectorAll('#fieldsContainer *')].every(element =>
            [...element.attributes].every(attribute => !attribute.name.startsWith('on'))), 'injected event attribute');
        options().forEach(input => input.focus());
        check(window.alerts.length === 0, 'payload executed');
    };
    const submit = () => {
        const event = new Event('submit', { bubbles: true, cancelable: true });
        document.getElementById('templateForm').dispatchEvent(event);
        check(!event.defaultPrevented, 'valid form rejected');
        return JSON.parse(document.getElementById('fieldsInput').value);
    };

    if (mode === 'create') {
        const invalid = new Event('submit', { cancelable: true });
        document.getElementById('templateForm').dispatchEvent(invalid);
        check(invalid.defaultPrevented && window.alerts.length === 1, 'empty-label validation changed');
        window.alerts = [];
        change(first().querySelector('.field-label'), payloads[0]);
        change(first().querySelector('.field-help'), payloads[1]);
        change(first().querySelector('.field-type'), 'checkbox', 'change');
        payloads.forEach((payload, index) => {
            if (index > 0) first().querySelector('.add-option').click();
            change(options()[index], payload);
        });
        // Force the same rerender that exposed attribute injection before the fix.
        first().querySelector('.add-option').click();
        first().querySelectorAll('.delete-option')[payloads.length].click();
    }

    assertOptions(payloads);
    check(first().querySelector('.field-label').value === payloads[0], 'label not preserved');
    check(first().querySelector('.field-help').value === payloads[1], 'help not preserved');
    const serialized = submit();
    check(serialized[0].label === payloads[0] && serialized[0].help === payloads[1], 'field serialization changed');
    check(JSON.stringify(serialized[0].options) === JSON.stringify(payloads), 'options serialization changed');

    change(options()[0], 'Edited option');
    first().querySelector('.add-option').click();
    change(options().at(-1), 'New option');
    first().querySelectorAll('.delete-option')[1].click();
    const expected = ['Edited option', ...payloads.slice(2), 'New option'];
    assertOptions(expected);
    first().querySelector('.duplicate-field').click();
    check(cards().length === 2, 'duplicate field failed');
    const duplicated = submit();
    check(duplicated[0].sort_order === 1 && duplicated[1].sort_order === 2, 'field order changed');
    check(JSON.stringify(duplicated[1].options) === JSON.stringify(expected), 'duplicate options changed');
    cards()[1].querySelector('.delete-field').click();
    document.getElementById('addFieldButton').click();
    check(cards().length === 2, 'add field failed');
    change(cards()[1].querySelector('.field-label'), 'Second field');
    check(submit().length === 2, 'add field serialization failed');
    cards()[1].querySelector('.delete-field').click();
    first().querySelector('.delete-field').click();
    check(cards().length === 1 && first().querySelector('.field-label').value === '', 'last-field fallback changed');
}

for (const mode of ['create', 'edit']) {
    test(`${mode}: payloads stay input values and form-builder actions still work`, async () => {
        const blade = await readFile(new URL(`../../resources/views/superadmin/templates/form-fields/${mode}.blade.php`, import.meta.url), 'utf8');
        let template = blade.match(/<template id="fieldTemplate">[\s\S]*?<\/template>/)[0];
        template = template.replace(/@foreach[^\n]*\n([\s\S]*?)@endforeach/g, (_, body) =>
            ['pdf', 'png'].map(extension => body
                .replaceAll('{{ $extension }}', extension)
                .replaceAll('{{ strtoupper($extension) }}', extension.toUpperCase())).join(''))
            .replace(/\{\{ config\('submissions.uploads.max_file_megabytes'\) \}\}/g, '10')
            .replace(/\{\{--[\s\S]*?--\}\}/g, '');
        let script = blade.match(/<script>([\s\S]*?)<\/script>/)[1];
        script = script
            .replaceAll("@json(config('submissions.uploads.allowed_extensions'))", '["pdf","png"]')
            .replaceAll("@json(config('submissions.uploads.max_file_megabytes'))", '10')
            .replaceAll("@json(implode(',', config('submissions.uploads.allowed_extensions')))", '"pdf,png"')
            .replaceAll('@json($fields ?? [])', scriptJson([{
                id: 'saved-field', label: payloads[0], type: 'checkbox', help: payloads[1],
                options: payloads, required: true, active: true,
            }]));
        assert.ok(!script.includes('@json'), 'unhandled Blade server value');
        const html = `<!doctype html><meta charset="utf-8">
            <form id="templateForm"><input id="fieldsInput" type="hidden"><div id="fieldsContainer"></div>
            <button id="addFieldButton" type="button">Add</button></form>${template}<pre id="result"></pre>
            <script>window.alerts=[];window.errors=[];window.alert=value=>window.alerts.push(value);
            window.addEventListener('error',event=>window.errors.push(event.message));</script>
            <script>${script}</script><script>
            document.addEventListener('DOMContentLoaded',()=>{
                try {
                    (${exercise.toString()})(${scriptJson(mode)},${scriptJson(payloads)});
                    setTimeout(()=>{document.getElementById('result').textContent=
                        window.alerts.length || window.errors.length ? JSON.stringify({alerts:window.alerts,errors:window.errors}) : 'PASS';},100);
                } catch(error) { document.getElementById('result').textContent=error.stack; }
            });</script>`;
        const directory = await mkdtemp(join(tmpdir(), 'form-builder-xss-'));
        try {
            const fixture = join(directory, 'fixture.html');
            await writeFile(fixture, html);
            const chrome = process.env.CHROME_BIN || (process.platform === 'win32'
                ? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe' : 'google-chrome');
            const { stdout } = await run(chrome, [
                '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
                '--disable-background-networking', `--user-data-dir=${join(directory, 'profile')}`,
                '--virtual-time-budget=1000', '--dump-dom', pathToFileURL(fixture).href,
            ], { windowsHide: true, timeout: 30000, maxBuffer: 4 * 1024 * 1024 });
            const result = stdout.match(/<pre id="result">([\s\S]*?)<\/pre>/)?.[1];
            assert.equal(result, 'PASS', result || 'Browser did not complete assertions');
        } finally {
            // Delete only this test's generated directory, never an existing browser profile.
            assert.ok(resolve(directory).startsWith(resolve(tmpdir()) + sep + 'form-builder-xss-'));
            await rm(directory, { recursive: true, force: true, maxRetries: 10, retryDelay: 200 });
        }
    });
}
