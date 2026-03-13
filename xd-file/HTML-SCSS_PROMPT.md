# XD デザインカンプ コーディングプロンプト

## 基本設定

XD から取得したデザインを、以下の規約に従って HTML+SCSS を出力してください。

## コーディング規約

```
src/scss/
├── foundation/        # 変数・関数・リセット・ベース
├── layout/            # l- レイアウト
├── object/
│   ├── component/     # c- 複数ページで使う共通コンポーネント
│   ├── project/pages/ # p- ページ固有スタイル
│   └── utility/       # u- ユーティリティ
```

- **BEM 記法**を使用してください
- **入れ子禁止**（ネストは使用しない）
- **rem()関数**を使用（px 値をそのまま prem() に括る。例：`font-size: rem(16);`）
  - **@use '../utility/variable' as \*;** の形式で変数を使用
  - **@import**は禁止。必ず **@use** を使用
  - letter-spacing は 「フォントの AV(トラッキング)の値 / 1000 」em となります

### 使用する変数

````scss
// カラー
variable.scssに書いてある変数使ってくれ

繰り返し使うものは変数としてあなたが設定して良いです

### メディアクエリ

```scss
// SP（768px以下）

@include mq {
  // SP用スタイル（768px以下）
}

- 該当するクラス名の中の最下部にincludeで記述すること
例）
.inner {
  max-width: rem(1120);
  padding: 0 var(--padding-pc);
  margin-inline: auto;
  @include mq {
    max-width: rem(600);
    padding: 0 var(--padding-sp);
  }
}

- PCと重複する記述は書かず、PC側の記述を残してください。

````

### HTML 規約

- **セマンティック HTML**を使用
- **alt 属性は内容を具体的に記述**

### コンポーネント例

#### ボタン

```scss
.button {
  min-width: rem(185);
  display: block;
  padding: rem(18) rem(25);
  width: fit-content;
  font-size: rem(14);
  font-weight: var(--bold);
  line-height: 1.65;
  letter-spacing: 0.04em;
  text-align: center;
  color: var(--white);
  background-color: var(--orange);
  border-radius: rem(1000);
  border: 1px solid var(--orange);
  transition: 0.3s ease-in-out all;
}
```

#### セクションタイトル

```scss
.section-title__en {
  font-size: rem(16);
  font-weight: var(--bold);
  font-family: var(--en-font);
  color: var(--orange);
  line-height: 1.4;
  letter-spacing: 0.1em;
}

.section-title__jp {
  font-size: rem(30);
  font-weight: var(--bold);
  color: var(--base-color);
  line-height: 1.5;
  letter-spacing: 0.2em;
  @include mq {
    font-size: rem(24);
  }
}
```

#### インナーコンテナ

```scss
.inner {
  max-width: rem(1120);
  padding: 0 var(--padding-pc);
  margin-inline: auto;
  @include mq {
    max-width: rem(600);
    padding: 0 var(--padding-sp);
  }
}
```

## 出力形式

1. **HTML 構造**（WordPress 用）
2. **SCSS ファイル**（components/または pages/フォルダ用）
3. **BEM 記法**に従ったクラス名
4. **レスポンシブ対応**（PC/SP）

## XD からのエクスポートについて

- XD からエクスポートした JSON ファイルと PNG 画像を使用してコーディングを行います。
- JSON ファイルには、アートボード、テキスト、レイアウト情報などが含まれています。
- PNG 画像は、レイアウト確認や実装後の検証に使用します。

## 注意事項

- 画像パスは`<?php echo get_template_directory_uri(); ?>/images/`を使用
- WordPress の関数を使用（`get_template_directory_uri()`など）
- フォーカス時のアウトラインは`outline: none`で削除済み
- ホバー効果は`@media (any-hover: hover)`で実装
