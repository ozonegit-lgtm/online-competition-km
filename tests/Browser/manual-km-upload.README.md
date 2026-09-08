# Manual KM browser upload regression

This test selects Office files in real Chrome, logs in through Laravel, and submits
the actual create/edit forms for both Super Admin and Competition Admin. It checks
multipart POST, the exact HTTP 302 `Location`, the resulting item, matching SHA256
of the private attachment, replacement cleanup, and an empty public disk.

The PHP server uses the application's Docker image and real Laravel HTTP kernel.
Sessions and CSRF remain enabled. Its SQLite database, users, private/public disks,
logs, compiled views, and Laravel cache files are created exclusively inside a new
`/tmp/manual-km-browser-*` directory in the disposable container. Laravel reads no
project `.env` file. The status endpoint exists only in this test server script;
it is not added to production routes.

Prerequisites: Docker app image/vendor volume, Node with built-in WebSocket, Chrome,
the existing `public/build` assets, and a directory containing valid `.docx` and
`.pptx` documents. The test uploads every DOCX/PPTX in that directory. Use actual
Office/LibreOffice documents when checking producer compatibility.

Start a separate container from PowerShell:

```powershell
docker compose run --rm --no-deps -d --name manual-km-browser --entrypoint sh --user www-data -p 127.0.0.1:18081:8000 -e MANUAL_KM_TEST_ROOT=/tmp/manual-km-browser-run1 app -c 'php tests/Browser/manual-km-server.php --init && exec php -S 0.0.0.0:8000 -t public tests/Browser/manual-km-server.php'
docker logs manual-km-browser
```

The `--entrypoint sh` override is required: the normal image entrypoint repairs
ownership and permissions of the shared production storage/cache directories.
Bypassing it prevents those operations. `--user www-data` also matches PHP-FPM's
upload user. Do not run initialization in the existing app container or start this
test server through the production entrypoint.

Once the log reports the HTTP server has started, run:

```powershell
$env:MANUAL_KM_FIXTURES_DIR = 'C:\path\to\valid-office-fixtures'
node --test tests/Browser/manual-km-upload.test.mjs
```

Set `CHROME_BIN` if Chrome is not at its standard Windows installation path.
`MANUAL_KM_TEST_ORIGIN` can override `http://127.0.0.1:18081`; use a loopback address
and pass the same origin into the server container if changing the port.

For a rejection regression, set `MANUAL_KM_EXPECT_ERROR` to the exact expected
validation message and point the fixture directory at invalid DOCX/PPTX files.
This mode verifies the browser returns to create with that error, HTTP 302, and
no persisted item or uploaded file. Unset the variable for success tests.

After the run, remove the disposable server and all its test data:

```powershell
docker stop manual-km-browser
Remove-Item Env:MANUAL_KM_FIXTURES_DIR -ErrorAction SilentlyContinue
```

The container uses `--rm`, so stopping it removes its temporary database/storage.
The Node test separately removes its generated Chrome profile. A second run needs
a fresh server; the test refuses a database containing items from an earlier run.

This exercises real browser uploads with the application's PHP runtime but uses a
dedicated PHP HTTP server, rather than the live nginx/FPM stack. It does not verify
a user's original failing file unless that exact file is included among fixtures.
