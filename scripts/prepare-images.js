/**
 * ビルド前：public/assets/images 内のJPG/PNGを圧縮し、WebPを同階層に生成
 * 出力: xxx.jpg → xxx.webp, xxx.png → xxx.webp（拡張子を.webpに置換）
 */

import sharp from 'sharp';
import { readdir, rename, unlink } from 'fs/promises';
import { join, extname, dirname } from 'path';
import { existsSync } from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');
const IMAGES_DIR = join(projectRoot, 'public/assets/images');
const SUPPORTED_EXT = /\.(jpg|jpeg|png)$/i;
const JPEG_QUALITY = 80;
const WEBP_QUALITY = 80;

const OLD_WEBP_EXT = /\.(jpe?g|png)\.webp$/i;

async function* walkDir(dir, fileFilter = (name) => SUPPORTED_EXT.test(name)) {
  if (!existsSync(dir)) return;
  const entries = await readdir(dir, { withFileTypes: true });
  for (const e of entries) {
    const full = join(dir, e.name);
    if (e.isDirectory() && !e.name.startsWith('.')) {
      yield* walkDir(full, fileFilter);
    } else if (e.isFile() && fileFilter(e.name)) {
      yield full;
    }
  }
}

async function removeOldWebpFiles() {
  const oldWebpFiles = [];
  for await (const f of walkDir(IMAGES_DIR, (name) => OLD_WEBP_EXT.test(name))) {
    oldWebpFiles.push(f);
  }
  for (const f of oldWebpFiles) {
    try {
      await unlink(f);
      console.log(`  🗑️ 削除: ${f.replace(IMAGES_DIR, '').replace(/^[/\\]/, '')}`);
    } catch (err) {
      console.error(`  ❌ 削除失敗 ${f}:`, err.message);
    }
  }
}

async function processImage(inputPath) {
  const ext = extname(inputPath).toLowerCase();
  const webpPath = inputPath.replace(/\.(jpe?g|png)$/i, '.webp');

  try {
    let pipeline = sharp(inputPath);

    if (ext === '.jpg' || ext === '.jpeg') {
      await pipeline
        .jpeg({ quality: JPEG_QUALITY, mozjpeg: true })
        .toFile(inputPath + '.tmp');
      await rename(inputPath + '.tmp', inputPath);
    }
    // PNGは非可逆圧縮で劣化するため上書きせず、WebPのみ生成

    await sharp(inputPath)
      .webp({ quality: WEBP_QUALITY })
      .toFile(webpPath);

    const rel = inputPath.replace(IMAGES_DIR, '').replace(/^[/\\]/, '');
    const webpRel = rel.replace(/\.(jpe?g|png)$/i, '.webp');
    console.log(`  ✅ ${rel} → ${webpRel}`);
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

  await removeOldWebpFiles();
  console.log(`\n🖼️ 画像圧縮＋WebP生成: ${files.length}件\n`);
  for (const f of files) await processImage(f);
  console.log('\n✨ 完了\n');
}

main();
