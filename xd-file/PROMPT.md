# Coding Prompt for xd-code Assets

## 実行指示

このファイルを読み込んだら、以下の手順を順番に実行し、該当箇所の HTML と SCSS を更新・追記してください。作業後は JSON と PNG を再確認して差異がないことを必ず検証します。

1. **対象アートボードを特定**

   - `export_0001.json` の `artboards` 配列から利用する `id` と `name` を抜き出す。
   - 同じ `guid` 名の PNG を `xd-code/` 内から取得して、レイアウト確認に使う。 -　これを 0001 　から順番に最後まで実行する

2. **テキスト情報の抽出**

   - JSON の `text` ノードから `fontFamily`, `fontSize`, `fontStyle`, `letterSpacing`, `fill` をメモする。
   - 見出し・ラベルなど用途ごとに分類して、クラス命名に反映。

3. **レイアウト情報の整理**

   - `bounds` の `width` / `height` / `x` / `y` を基に、余白・並び順を推定。
   - 画像では余白の相対感を確認、必要に応じて実寸を `sips -g pixelWidth -g pixelHeight <path>` で計測。
   - 小さめのレイアウトは「共通パーツ」と判断し、`HTML-SCSS_PROMPT.md` のコーディング規約に従って HTML と SCSS を記述してください。
   - 大まかなレイアウトは json ではなく、画像を参照して組み立ててください。

4. **HTML と SCSS の記述**

   - HTML と SCSS の記述については、`HTML-SCSS_PROMPT.md` のコーディング規約に従ってください。
   - JSON に記載の値を優先し、調整が必要な箇所はコメントで理由を残す。

5. **確認フロー**
   - 実装後に JSON を再チェックし、反映漏れの数値がないかをリストアップ。
   - SASS ビルドを走らせ、ブラウザで PNG と並べて見比べて差異を修正。
