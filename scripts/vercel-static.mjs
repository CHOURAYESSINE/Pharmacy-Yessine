import { cp, mkdir, rm } from 'node:fs/promises';
import path from 'node:path';

const output = 'vercel-public';
await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
await cp('public', output, {
  recursive: true,
  filter: (source) => {
    const relative = path.relative('public', source).replaceAll('\\', '/');
    return !relative.endsWith('.php') && relative !== 'hot'
      && relative !== 'storage' && !relative.startsWith('storage/');
  },
});
