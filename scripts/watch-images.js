/**
 * 画像フォルダ監視 → WebP自動変換（build不要）
 * sharp + chokidar で original/ を監視し、webp/ に自動出力
 *
 * 使い方:
 *   npm run watch:images     # 監視開始（常時）
 *   npm run convert:images   # 1回だけ変換
 *
 * フォルダ構成:
 *   public/assets/images/original/  ← ここにJPG/PNGを入れる
 *   public/assets/images/webp/     ← ここにWebPが自動生成される
 */

import sharp from 'sharp';
import chokidar from 'chokidar';
import { fileURLToPath } from 'url';
import { dirname, resolve, join, extname } from 'path';
import { mkdir, unlink } from 'fs/promises';
import { existsSync } from 'fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = resolve(__dirname, '..');

const ORIGINAL_DIR = resolve(projectRoot, 'public/assets/images/original');
const WEBP_DIR = resolve(projectRoot, 'public/assets/images/webp');
const SUPPORTED_EXT = /\.(jpg|jpeg|png)$/i;
const WEBP_QUALITY = 80;

async function convertToWebp(inputPath) {
  const ext = extname(inputPath);
  if (!SUPPORTED_EXT.test(ext)) return;

  const basename = inputPath.replace(/\.[^.]+$/, '');
  const relativePath = basename.replace(ORIGINAL_DIR, '').replace(/^\//, '');
  const outputPath = join(WEBP_DIR, `${relativePath}.webp`);

  try {
    await mkdir(dirname(outputPath), { recursive: true });
    await sharp(inputPath)
      .webp({ quality: WEBP_QUALITY })
      .toFile(outputPath);
    console.log(`✅ ${relativePath}.webp`);
  } catch (err) {
    console.error(`❌ ${inputPath}:`, err.message);
  }
}

async function removeWebp(inputPath) {
  const ext = extname(inputPath);
  if (!SUPPORTED_EXT.test(ext)) return;

  const basename = inputPath.replace(/\.[^.]+$/, '');
  const relativePath = basename.replace(ORIGINAL_DIR, '').replace(/^\//, '');
  const outputPath = join(WEBP_DIR, `${relativePath}.webp`);

  try {
    if (existsSync(outputPath)) {
      await unlink(outputPath);
      console.log(`🗑️ 削除: ${relativePath}.webp`);
    }
  } catch (err) {
    console.error(`❌ 削除失敗 ${outputPath}:`, err.message);
  }
}

async function processAll() {
  if (!existsSync(ORIGINAL_DIR)) {
    console.log('📁 original フォルダがありません。');
    console.log(`   ${ORIGINAL_DIR} を作成してJPG/PNGを配置してください。`);
    return;
  }
  const { glob } = await import('glob');
  const files = await glob('**/*.{jpg,jpeg,png}', { cwd: ORIGINAL_DIR });
  const fullPaths = files.map((f) => join(ORIGINAL_DIR, f));
  if (fullPaths.length === 0) {
    console.log('📁 変換対象の画像がありません。');
    console.log(`   ${ORIGINAL_DIR} にJPG/PNGを配置してください。`);
    return;
  }
  console.log(`🖼️ ${fullPaths.length}件の画像を変換します...\n`);
  for (const f of fullPaths) await convertToWebp(f);
  console.log('\n✨ 完了');
}

async function watch() {
  await mkdir(ORIGINAL_DIR, { recursive: true });
  await mkdir(WEBP_DIR, { recursive: true });

  const watcher = chokidar.watch(ORIGINAL_DIR, {
    ignored: /(^|[\/\\])\../,
    persistent: true,
  });

  watcher
    .on('add', (path) => convertToWebp(path))
    .on('change', (path) => convertToWebp(path))
    .on('unlink', (path) => removeWebp(path));

  console.log('👀 画像監視を開始しました（build不要）');
  console.log(`   original: ${ORIGINAL_DIR}`);
  console.log(`   webp:     ${WEBP_DIR}`);
  console.log('   JPG/PNGを original/ に入れると自動でWebPが生成されます。\n');
}

const isWatch = process.argv.includes('--watch');
if (isWatch) {
  watch();
} else {
  processAll();
}
