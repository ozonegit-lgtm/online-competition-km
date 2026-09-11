import { rmSync } from 'node:fs';

rmSync(new URL('../public/hot', import.meta.url), { force: true });
