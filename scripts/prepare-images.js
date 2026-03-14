/**
 * ビルド前：public/assets/images 内のJPG/PNGを圧縮
 * 出力: xxx.jpg → 圧縮上書き, xxx.png → 圧縮上書き
 * WebP変換は行わない
 */

import sharp from 'sharp';
import { readdir, rename } from 'fs/promises';
import { join, extname, dirname } from 'path';
import { existsSync } from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');
const IMAGES_DIR = join(projectRoot, 'public/assets/images');
const SUPPORTED_EXT = /\.(jpg|jpeg|png)$/i;
const JPEG_QUALITY = 80;
const PNG_COMPRESSION = 9; // 0-9, 9=最大圧縮（可逆）

async function* walkDir(dir) {
  if (!existsSync(dir)) return;
  const entries = await readdir(dir, { withFileTypes: true });
  for (const e of entries) {
    const full = join(dir, e.name);
    if (e.isDirectory() && !e.name.startsWith('.')) {
      yield* walkDir(full);
    } else if (e.isFile() && SUPPORTED_EXT.test(e.name)) {
      yield full;
    }
  }
}

async function processImage(inputPath) {
  const ext = extname(inputPath).toLowerCase();

  try {
    if (ext === '.jpg' || ext === '.jpeg') {
      await sharp(inputPath)
        .jpeg({ quality: JPEG_QUALITY, mozjpeg: true })
        .toFile(inputPath + '.tmp');
      await rename(inputPath + '.tmp', inputPath);
    } else if (ext === '.png') {
      await sharp(inputPath)
        .png({ compressionLevel: PNG_COMPRESSION })
        .toFile(inputPath + '.tmp');
      await rename(inputPath + '.tmp', inputPath);
    }

    const rel = inputPath.replace(IMAGES_DIR, '').replace(/^[/\\]/, '');
    console.log(`  ✅ 圧縮: ${rel}`);
  } catch (err) {
    console.error(`  ❌ ${inputPath}:`, err.message);
  }
}

async function main() {
  if (!existsSync(IMAGES_DIR)) {
    console.log('📁 public/assets/images がありません。スキップします。');
    return;
  }

  const files = [];
  for await (const f of walkDir(IMAGES_DIR)) files.push(f);

  if (files.length === 0) {
    console.log('📁 処理対象のJPG/PNGがありません。');
    return;
  }

  console.log(`\n🖼️ 画像圧縮（PNG/JPG）: ${files.length}件\n`);
  for (const f of files) await processImage(f);
  console.log('\n✨ 完了\n');
}

main();
