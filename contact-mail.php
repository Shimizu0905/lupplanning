<?php
// エラー表示を有効にする（デバッグ用）
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Content-Type:text/html;charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ./#contact');
	exit();
}

// デバッグ用：フォーム送信をテスト（コメントアウト）
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     echo "<!-- フォームが送信されました -->";
//     echo "<!-- POSTデータ: " . print_r($_POST, true) . " -->";
// } else {
//     echo "<!-- GETリクエストです -->";
// }
?>
<?php //error_reporting(E_ALL | E_STRICT);
##-----------------------------------------------------------------------------------------------------------------##
#
#  PHPメールプログラム　フリー版 ver2.0.4 最終更新日2024/05/24
#　改造や改変は自己責任で行ってください。
#
#  重要！！サイトでチェックボックスを使用する場合のみですが。。。
#  チェックボックスを使用する場合はinputタグに記述するname属性の値を必ず配列の形にしてください。
#  例　name="当サイトをしったきっかけ[]"  として下さい。
#  nameの値の最後に[と]を付ける。じゃないと複数の値を取得できません！
#
##-----------------------------------------------------------------------------------------------------------------##

if (version_compare(PHP_VERSION, '5.1.0', '>=')) { //PHP5.1.0以上の場合のみタイムゾーンを定義
	date_default_timezone_set('Asia/Tokyo'); //タイムゾーンの設定（日本以外の場合には適宜設定ください）
}


//サイトのトップページのURL　※デフォルトでは送信完了後に「トップページへ戻る」ボタンが表示され、そのリンク先です。
$site_top = "./";

//管理者のメールアドレス（送信先） ※メールを受け取るメールアドレス(複数指定する場合は「,」で区切ってください 例 $to = "aa@aa.aa,bb@bb.bb";)
$to = "info@lupplanning.com";

//送信元（差出人）メールアドレス（管理者宛て、及びユーザー宛メールの送信元（差出人）メールアドレスです）
//必ず実在するメールアドレスでかつ出来る限り設置先サイトのドメインと同じドメインのメールアドレスとしてください（でないと「なりすまし」扱いされます）
//管理者宛てメールの返信先（reply）はユーザーが入力したメールアドレスになりますので返信時はユーザーのメールアドレスが送信先に設定されます）
$from = "info@lupplanning.com";

//管理者宛メールの送信元（差出人）にユーザーが入力したメールアドレスを表示する(する=1, しない=0)
//ユーザーのメールアドレスを含めることでメーラー上で管理しやすくなる機能です。
//例 example@gmail.com <from@sample.jp>（example@gmail.comがユーザーメールアドレス、from@sample.jpが↑の$fromで設定したメールアドレスです）
$from_add = 1;

//フォームのメールアドレス入力箇所のname属性の値（name="○○"　の○○部分）
$Email = "email";
//---------------------------　必須設定　ここまで　------------------------------------


//---------------------------　セキュリティ、スパム防止のための設定　------------------------------------

//スパム防止のためのリファラチェック（フォーム側とこのファイルが同一ドメインであるかどうかのチェック）(する=1, しない=0)
//※有効にするにはこのファイルとフォームのページが同一ドメイン内にある必要があります
$Referer_check = 1;

//リファラチェックを「する」場合のドメイン ※設置するサイトのドメインを指定して下さい。
//もしこの設定が間違っている場合は送信テストですぐに気付けます。
$Referer_check_domain = $_SERVER['HTTP_HOST'] ?? "";

/*セッションによるワンタイムトークン（CSRF対策、及びスパム防止）(する=1, しない=0)
※ただし、この機能を使う場合は↓の送信確認画面の表示が必須です。（デフォルトではON（1）になっています）
※【重要】ガラケーは機種によってはクッキーが使えないためガラケーの利用も想定してる場合は「0」（OFF）にして下さい（PC、スマホは問題ないです）*/
$useToken = 0;
//---------------------------　セキュリティ、スパム防止のための設定　ここまで　------------------------------------


//---------------------- 任意設定　以下は必要に応じて設定してください ------------------------

// Bccで送るメールアドレス(複数指定する場合は「,」で区切ってください 例 $BccMail = "aa@aa.aa,bb@bb.bb";)
$BccMail = "";

// 管理者宛に送信されるメールのタイトル（件名）
$subject = "【お問い合わせ】LUP Planning ホームページからのお問い合わせ";

// 送信確認画面の表示(する=1, しない=0)
$confirmDsp = 0;

// 送信完了後に自動的に指定のページ(サンクスページなど)に移動する(する=1, しない=0)
// CV率を解析したい場合などはサンクスページを別途用意し、URLをこの下の項目で指定してください。
// 0にすると、デフォルトの送信完了画面が表示されます。
$jumpPage = 1;

// 送信完了後に表示するページURL（上記で1を設定した場合のみ）※httpから始まるURLで指定ください。（相対パスでも基本的には問題ないです）
$thanksPage = "contact-thanks.html";

// 必須入力項目を設定する(する=1, しない=0)
$requireCheck = 1;

/* 必須入力項目(入力フォームで指定したname属性の値を指定してください。（上記で1を設定した場合のみ）
値はシングルクォーテーションで囲み、複数の場合はカンマで区切ってください。フォーム側と順番を合わせると良いです。
配列の形「name="○○[]"」の場合には必ず後ろの[]を取ったものを指定して下さい。*/
$require = array('inquiry_type', 'name', 'kana', 'tel', 'email', 'address', 'privacy'); // 必須にしたい項目


//----------------------------------------------------------------------
//  自動返信メール設定(START)
//----------------------------------------------------------------------

// 差出人に送信内容確認メール（自動返信メール）を送る(送る=1, 送らない=0)
// 送る場合は、フォーム側のメール入力欄のname属性の値が上記「$Email」で指定した値と同じである必要があります
$remail = 1;

//自動返信メールの送信者欄に表示される名前　※あなたの名前や会社名など（もし自動返信メールの送信者名が文字化けする場合ここは空にしてください）
$refrom_name = "合同会社 LUP Planning";

// 差出人に送信確認メールを送る場合のメールのタイトル（上記で1を設定した場合のみ）
$re_subject = "【自動返信】お問い合わせを受け付けました - 合同会社 LUP Planning";

//フォーム側の「名前」箇所のname属性の値　※自動返信メールの「○○様」の表示で使用します。
//指定しない、または存在しない場合は、○○様と表示されないだけです。あえて無効にしてもOK
$dsp_name = 'name';

//自動返信メールの冒頭の文言 ※日本語部分のみ変更可
$remail_text = <<< TEXT

この度は、合同会社 LUP Planning にお問い合わせいただき、誠にありがとうございます。

お問い合わせ内容を確認いたしました。
担当者より2営業日以内にご返信いたします。

お急ぎの場合は、お電話（090-4271-5007）にてお問い合わせください。

送信いただいた内容は以下の通りです。

TEXT;


//自動返信メールに署名（フッター）を表示(する=1, しない=0)※管理者宛にも表示されます。
$mailFooterDsp = 0;

//上記で「1」を選択時に表示する署名（フッター）（FOOTER～FOOTER;の間に記述してください）
$mailSignature = <<< FOOTER

──────────────────────
株式会社○○○○　佐藤太郎
〒150-XXXX 東京都○○区○○ 　○○ビル○F　
TEL：03- XXXX - XXXX 　FAX：03- XXXX - XXXX
携帯：090- XXXX - XXXX 　
E-mail:xxxx@xxxx.com
URL: http://www.php-factory.net/
──────────────────────

FOOTER;


//----------------------------------------------------------------------
//  自動返信メール設定(END)
//----------------------------------------------------------------------

//メールアドレスの形式チェックを行うかどうか。(する=1, しない=0)
//※デフォルトは「する」。特に理由がなければ変更しないで下さい。メール入力欄のname属性の値が上記「$Email」で指定した値である必要があります。
$mail_check = 1;

//全角英数字→半角変換を行うかどうか。(する=1, しない=0)
$hankaku = 0;

//全角英数字→半角変換を行う項目のname属性の値（name="○○"の「○○」部分）
//※複数の場合にはカンマで区切って下さい。（上記で「1」を指定した場合のみ有効）
//配列の形「name="○○[]"」の場合には必ず後ろの[]を取ったものを指定して下さい。
$hankaku_array = array('tel');

//-fオプションによるエンベロープFrom（Return-Path）の設定(する=1, しない=0)　
//※宛先不明（間違いなどで存在しないアドレス）の場合に 管理者宛に「Mail Delivery System」から「Undelivered Mail Returned to Sender」というメールが届きます。
//サーバーによっては稀にこの設定が必須の場合もあります。
//設置サーバーでPHPがセーフモードで動作している場合は使用できませんので送信時にエラーが出たりメールが届かない場合は「0」（OFF）として下さい。
$use_envelope = 0;

//機種依存文字の変換
/*たとえば㈱（かっこ株）や①（丸1）、その他特殊な記号や特殊な漢字などは変換できずに「？」と表示されます。それを回避するための機能です。
確認画面表示時に置換処理されます。「変換前の文字」が「変換後の文字」に変換され、送信メール内でも変換された状態で送信されます。（たとえば「㈱」の場合、「（株）」に変換されます）
必要に応じて自由に追加して下さい。ただし、変換前の文字と変換後の文字の順番と数は必ず合わせる必要がありますのでご注意下さい。*/

//変換前の文字
$replaceStr['before'] = array('①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨', '⑩', '№', '㈲', '㈱', '髙');
//変換後の文字
$replaceStr['after'] = array('(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', 'No.', '（有）', '（株）', '高');

//------------------------------- 任意設定ここまで ---------------------------------------------


// 以下の変更は知識のある方のみ自己責任でお願いします。

//----------------------------------------------------------------------
//  関数実行、変数初期化
//----------------------------------------------------------------------
//トークンチェック用のセッションスタート
if ($useToken == 1 && $confirmDsp == 1) {
	session_name('PHPMAILFORMSYSTEM');
	session_start();
}
$encode = "UTF-8"; //このファイルの文字コード定義（変更不可）
if (isset($_GET)) $_GET = sanitize($_GET); //NULLバイト除去//
if (isset($_POST)) $_POST = sanitize($_POST); //NULLバイト除去//
if (isset($_COOKIE)) $_COOKIE = sanitize($_COOKIE); //NULLバイト除去//
if ($encode == 'SJIS') $_POST = sjisReplace($_POST, $encode); //Shift-JISの場合に誤変換文字の置換実行
$funcRefererCheck = refererCheck($Referer_check, $Referer_check_domain); //リファラチェック実行

//変数初期化
$sendmail = 0;
$empty_flag = 0;
$post_mail = '';
$errm = '';
$header = '';

if (!empty($_POST['website'])) {
	http_response_code(400);
	exit('不正な送信です。');
}

if ($requireCheck == 1) {
	$requireResArray = requireCheck($require); //必須チェック実行し返り値を受け取る
	$errm = $requireResArray['errm'];
	$empty_flag = $requireResArray['empty_flag'];
}
//メールアドレスチェック
if (empty($errm)) {
	foreach ($_POST as $key => $val) {
		if ($val == "confirm_submit") $sendmail = 1;
		if ($key == $Email) $post_mail = h($val);
		if ($key == $Email && $mail_check == 1 && !empty($val)) {
			if (!checkMail($val)) {
				$errm .= "<p class=\"error_messe\">【" . $key . "】はメールアドレスの形式が正しくありません。</p>\n";
				$empty_flag = 1;
			}
		}
	}
}

if (($confirmDsp == 0 || $sendmail == 1) && $empty_flag != 1) {

	//トークンチェック（CSRF対策）※確認画面がONの場合のみ実施
	if ($useToken == 1 && $confirmDsp == 1) {
		if (empty($_SESSION['mailform_token']) || ($_SESSION['mailform_token'] !== $_POST['mailform_token'])) {
			exit('ページ遷移が不正です');
		}
		if (isset($_SESSION['mailform_token'])) unset($_SESSION['mailform_token']); //トークン破棄
		if (isset($_POST['mailform_token'])) unset($_POST['mailform_token']); //トークン破棄
	}

	//差出人に届くメールをセット
	if ($remail == 1) {
		$userBody = mailToUser($_POST, $dsp_name, $remail_text, $mailFooterDsp, $mailSignature, $encode);
		$reheader = userHeader($refrom_name, $from, $encode);
		$re_subject = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($re_subject, "JIS", $encode)) . "?=";
	}
	//管理者宛に届くメールをセット
	$adminBody = mailToAdmin($_POST, $subject, $mailFooterDsp, $mailSignature, $encode, $confirmDsp);
	$header = adminHeader($post_mail, $BccMail);
	$subject = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($subject, "JIS", $encode)) . "?=";

	//-fオプションによるエンベロープFrom（Return-Path）の設定(safe_modeがOFFの場合かつ上記設定がONの場合のみ実施)
	if ($use_envelope == 0) {
		mail($to, $subject, $adminBody, $header);
		if ($remail == 1 && !empty($post_mail)) {
			mail($post_mail, $re_subject, $userBody, $reheader);
		}
	} else {
		mail($to, $subject, $adminBody, $header, '-f' . $from);
		if ($remail == 1 && !empty($post_mail)) {
			mail($post_mail, $re_subject, $userBody, $reheader, '-f' . $from);
		}
	}
} else if ($confirmDsp == 1 && $sendmail != 1) {

	/*　▼▼▼送信確認画面のレイアウト※編集可　index.htmlの構造に合わせる▼▼▼　*/
?>
	<!DOCTYPE html>
	<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>お問い合わせ確認 | LUP Planning</title>
		<meta name="description" content="AI搭載防犯カメラで侵入前に光と音で撃退。設置後も徹底フォローする人間力の防犯をご提供します。">
		<link rel="stylesheet" href="./assets/css/style.css">
		<script type="module" src="./assets/js/main.js"></script>
	</head>
	<body>
		<div id="page" class="l-site">
			<header class="p-header" id="header">
				<div class="p-header__inner">
					<h1 class="p-header__logo">
						<a href="./" class="p-header__logo-link">
							<img src="./assets/images/common/logo.png" alt="LUP Planning" class="p-header__logo-img">
						</a>
					</h1>
					<div class="p-header__btn-box">
						<a href="./#contact" class="c-btn c-btn--primary p-header__btn">お問い合わせ</a>
					</div>
				</div>
			</header>

			<main class="l-main">
				<section class="p-contact" id="contact">
					<div class="p-contact__inner">
						<div class="p-contact__header">
							<p class="p-contact__label">CONTACT</p>
							<h2 class="p-contact__title">お問い合わせ確認</h2>
						</div>
						<p class="p-contact__confirm-text" style="margin-bottom: 2rem; text-align: center;">以下の内容で間違いがなければ、「送信する」ボタンを押してください。</p>

						<?php if ($empty_flag == 1) { ?>
							<div class="p-contact__error" style="padding: 2rem; margin-bottom: 2rem; background: #fff3f3; border: 1px solid #f00; border-radius: 8px;">
								<h4 style="margin: 0 0 1rem; color: #c00;">入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4>
								<?php echo $errm; ?>
								<div style="margin-top: 1.5rem;">
									<button type="button" class="c-btn c-btn--primary" onclick="history.back()">前画面に戻る</button>
								</div>
							</div>
						<?php } else { ?>
							<form action="<?php echo h($_SERVER['SCRIPT_NAME']); ?>" method="POST" class="p-contact__form">
								<div class="p-contact__confirm-list" style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem; padding: 2rem; background: #f8f8f8; border-radius: 8px;">
									<?php echo confirmOutput($_POST); ?>
								</div>
								<div style="display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap;">
									<input type="hidden" name="mail_set" value="confirm_submit">
									<input type="hidden" name="httpReferer" value="<?php echo h($_SERVER['HTTP_REFERER'] ?? ''); ?>">
									<button type="button" class="c-btn" onclick="history.back()" style="background: #fff; border: 2px solid #333;">前画面に戻る</button>
									<button type="submit" class="c-btn c-btn--primary p-contact__form-submit">送信する</button>
								</div>
							</form>
						<?php } ?>
					</div>
				</section>
			</main>

			<footer class="p-footer">
				<div class="p-footer__inner">
					<p class="p-footer__copyright">Copyright © 合同会社 LUP Planning All Rights Reserved.</p>
				</div>
			</footer>
		</div>
	</body>
	</html>
<?php
	/* ▲▲▲送信確認画面のレイアウト▲▲▲　*/
}

if (($jumpPage == 0 && $sendmail == 1) || ($jumpPage == 0 && ($confirmDsp == 0 && $sendmail == 0))) {

	/* ▼▼▼送信完了画面のレイアウト　編集可 ※送信完了後に指定のページに移動しない場合のみ表示▼▼▼　*/
?>
	<!DOCTYPE HTML>
	<html lang="ja">

	<head>
		<!-- Google Tag Manager -->
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-WH6G86BQ');
		</script>
		<!-- End Google Tag Manager -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="format-detection" content="telephone=no">
		<title>完了画面</title>
		<link rel="stylesheet" href="./style.css">
		<script src="./main.js" defer></script>
	</head>

	<body>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WH6G86BQ"
				height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<!-- ヘッダーはJavaScriptで動的に読み込み -->
		<div id="header-container"></div>
		<div align="center">
			<?php if ($empty_flag == 1) { ?>
				<h4>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4>
				<div style="color:red"><?php echo $errm; ?></div>
				<br /><br /><input type="button" value=" 前画面に戻る " onClick="history.back()">
		</div>
	</body>

	</html>
<?php } else { ?>
	送信ありがとうございました。<br />
	送信は正常に完了しました。<br /><br />
	<a href="<?php echo $site_top; ?>">トップページへ戻る&raquo;</a>
	</div>
	<?php copyright(); ?>
	<!--  CV率を計測する場合ここにAnalyticsコードを貼り付け -->
	</body>

	</html>
<?php
				/* ▲▲▲送信完了画面のレイアウト 編集可 ※送信完了後に指定のページに移動しない場合のみ表示▲▲▲　*/
			}
		}
		//確認画面無しの場合の表示、指定のページに移動する設定の場合、エラーチェックで問題が無ければ指定ページヘリダイレクト
		else if (($jumpPage == 1 && $sendmail == 1) || $confirmDsp == 0) {
			if ($empty_flag == 1) { ?>
	<div align="center">
		<h4>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4>
		<div style="color:red"><?php echo $errm; ?></div><br /><br /><input type="button" value=" 前画面に戻る " onClick="history.back()">
	</div>
<?php
			} else {
				// 出力バッファをクリアしてからリダイレクト
				if (ob_get_level()) {
					ob_end_clean();
				}
				header("Location: " . $thanksPage);
				exit();
			}
		}

		// 以下の変更は知識のある方のみ自己責任でお願いします。

		//----------------------------------------------------------------------
		//  関数定義(START)
		//----------------------------------------------------------------------
		function checkMail($str)
		{
			$mailaddress_array = explode('@', $str);
			if (preg_match("/^[\.!#%&\-_0-9a-zA-Z\?\/\+]+\@[!#%&\-_0-9a-zA-Z]+(\.[!#%&\-_0-9a-zA-Z]+)+$/", "$str") && count($mailaddress_array) == 2) {
				return true;
			} else {
				return false;
			}
		}
		function h($string)
		{
			global $encode;
			return htmlspecialchars($string, ENT_QUOTES, $encode);
		}
		function sanitize($arr)
		{
			if (is_array($arr)) {
				return array_map('sanitize', $arr);
			}
			return str_replace("\0", "", $arr);
		}
		//Shift-JISの場合に誤変換文字の置換関数
		function sjisReplace($arr, $encode)
		{
			foreach ($arr as $key => $val) {
				$key = str_replace('＼', 'ー', $key);
				$resArray[$key] = $val;
			}
			return $resArray;
		}
		//送信メールにPOSTデータをセットする関数
		function postToMail($arr)
		{
			global $hankaku, $hankaku_array;
			$resArray = '';

			// フィールド名の日本語マッピング
			$fieldNames = array(
				'inquiry_type' => 'お問い合わせ内容',
				'name' => 'お名前',
				'kana' => 'フリガナ',
				'tel' => 'お電話番号',
				'email' => 'メールアドレス',
				'address' => '設置先ご住所',
				'message' => '自由記入欄',
				'privacy' => 'プライバシーポリシー'
			);

			foreach ($arr as $key => $val) {
				$out = '';
				if (is_array($val)) {
					foreach ($val as $key02 => $item) {
						//連結項目の処理
						if (is_array($item)) {
							$out .= connect2val($item);
						} else {
							$out .= $item . ', ';
						}
					}
					$out = rtrim($out, ', ');
				} else {
					$out = $val;
				} //チェックボックス（配列）追記ここまで

				// get_magic_quotes_gpcはPHP 7.4で削除されたため、この処理は無効化
				// if (version_compare(PHP_VERSION, '5.1.0', '<=') && function_exists('get_magic_quotes_gpc')) {
				// 	if (get_magic_quotes_gpc()) {
				// 		$out = stripslashes($out);
				// 	}
				// }

				//全角→半角変換
				if ($hankaku == 1) {
					$out = zenkaku2hankaku($key, $out, $hankaku_array);
				}
				if ($out != "confirm_submit" && $key != "httpReferer") {
					// フィールド名を日本語に変換
					$displayKey = isset($fieldNames[$key]) ? $fieldNames[$key] : $key;
					$resArray .= "【 " . h($displayKey) . " 】 " . h($out) . "\n";
				}
			}
			return $resArray;
		}
		//確認画面の入力内容出力用関数
		function confirmOutput($arr)
		{
			global $hankaku, $hankaku_array, $useToken, $confirmDsp, $replaceStr;
			$html = '';

			// 項目名の日本語変換テーブル
			$fieldNames = array(
				'inquiry_type' => 'お問い合わせ内容',
				'name' => 'お名前',
				'kana' => 'フリガナ',
				'tel' => 'お電話番号',
				'email' => 'メールアドレス',
				'address' => '設置先ご住所',
				'message' => '自由記入欄',
				'privacy' => 'プライバシーポリシー' // 除外するので使用されない
			);

			foreach ($arr as $key => $val) {
				// 確認・送信用の隠し項目はスキップ
				if (in_array($key, array('mail_set', 'httpReferer', 'confirm_submit'))) {
					continue;
				}
				// プライバシー項目は特別処理
				if ($key === 'privacy') {
					$displayKey = 'プライバシーポリシー';
					$out = '同意済み';

					$html .= '<div class="p-contact__form-item">';
					$html .= '<div class="p-contact__form-label">' . h($displayKey) . '</div>';
					$html .= '<div style="padding: 0.5rem 0;">' . h($out);
					$html .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
					$html .= '</div></div>';
					continue;
				}

				$out = '';
				if (is_array($val)) {
					foreach ($val as $key02 => $item) {
						//連結項目の処理
						if (is_array($item)) {
							$out .= connect2val($item);
						} else {
							$out .= $item . ', ';
						}
					}
					$out = rtrim($out, ', ');
				} else {
					$out = $val;
				} //チェックボックス（配列）追記ここまで

				// get_magic_quotes_gpcはPHP 7.4で削除されたため、この処理は無効化
				// if (version_compare(PHP_VERSION, '5.1.0', '<=') && function_exists('get_magic_quotes_gpc')) {
				// 	if (get_magic_quotes_gpc()) {
				// 		$out = stripslashes($out);
				// 	}
				// }

				//全角→半角変換
				if ($hankaku == 1) {
					$out = zenkaku2hankaku($key, $out, $hankaku_array);
				}

				$out = nl2br(h($out)); //※追記 改行コードを<br>タグに変換

				// 項目名を日本語に変換
				$displayKey = isset($fieldNames[$key]) ? $fieldNames[$key] : $key;
				$displayKey = h($displayKey);
				$out = str_replace($replaceStr['before'], $replaceStr['after'], $out); //機種依存文字の置換処理

				// p-contact用のHTML形式に変更
				$hiddenVal = is_array($val) ? implode(',', $val) : $val;
				$html .= '<div class="p-contact__form-item">';
				$html .= '<div class="p-contact__form-label">' . $displayKey . '</div>';
				$html .= '<div style="padding: 0.5rem 0;">' . $out;
				$html .= '<input type="hidden" name="' . h($key) . '" value="' . h($hiddenVal) . '" />';
				$html .= '</div></div>';
			}
			//トークンをセット
			if ($useToken == 1 && $confirmDsp == 1) {
				$token = sha1(uniqid(mt_rand(), true));
				$_SESSION['mailform_token'] = $token;
				$html .= '<input type="hidden" name="mailform_token" value="' . $token . '" />';
			}

			return $html;
		}

		//全角→半角変換
		function zenkaku2hankaku($key, $out, $hankaku_array)
		{
			global $encode;
			if (is_array($hankaku_array) && function_exists('mb_convert_kana')) {
				foreach ($hankaku_array as $hankaku_array_val) {
					if ($key == $hankaku_array_val) {
						$out = mb_convert_kana($out, 'a', $encode);
					}
				}
			}
			return $out;
		}
		//配列連結の処理
		function connect2val($arr)
		{
			$out = '';
			foreach ($arr as $key => $val) {
				if ($key === 0 || $val == '') { //配列が未記入（0）、または内容が空のの場合には連結文字を付加しない（型まで調べる必要あり）
					$key = '';
				} elseif (strpos($key, "円") !== false && $val != '' && preg_match("/^[0-9]+$/", $val)) {
					$val = number_format($val); //金額の場合には3桁ごとにカンマを追加
				}
				$out .= $val . $key;
			}
			return $out;
		}

		//管理者宛送信メールヘッダ
		function adminHeader($post_mail, $BccMail)
		{
			global $from, $from_add;
			$header = "From: ";
			if (!empty($post_mail) && $from_add == 1) {
				$header .= mb_encode_mimeheader('"' . $post_mail . '"') . " <" . $from . ">\n";
			} else {
				$header .= $from . "\n";
			}
			if ($BccMail != '') {
				$header .= "Bcc: $BccMail\n";
			}
			if (!empty($post_mail)) {
				$header .= "Reply-To: " . $post_mail . "\n";
			}
			$header .= "Content-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/" . phpversion();
			return $header;
		}
		//管理者宛送信メールボディ
		function mailToAdmin($arr, $subject, $mailFooterDsp, $mailSignature, $encode, $confirmDsp)
		{
			$adminBody = "「" . $subject . "」からメールが届きました\n\n";
			$adminBody .= "＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
			$adminBody .= postToMail($arr); //POSTデータを関数からセット
			$adminBody .= "\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n";
			$adminBody .= "送信された日時：" . date("Y/m/d (D) H:i:s", time()) . "\n";
			$adminBody .= "送信者のIPアドレス：" . @$_SERVER["REMOTE_ADDR"] . "\n";
			$adminBody .= "送信者のホスト名：" . getHostByAddr(getenv('REMOTE_ADDR')) . "\n";
			if ($confirmDsp != 1) {
				$adminBody .= "問い合わせのページURL：" . @$_SERVER['HTTP_REFERER'] . "\n";
			} else {
				$adminBody .= "問い合わせのページURL：" . @$arr['httpReferer'] . "\n";
			}
			if ($mailFooterDsp == 1) $adminBody .= $mailSignature;
			return mb_convert_encoding($adminBody, "JIS", $encode);
		}

		//ユーザ宛送信メールヘッダ
		function userHeader($refrom_name, $to, $encode)
		{
			$reheader = "From: ";
			if (!empty($refrom_name)) {
				$default_internal_encode = mb_internal_encoding();
				if ($default_internal_encode != $encode) {
					mb_internal_encoding($encode);
				}
				$reheader .= mb_encode_mimeheader($refrom_name) . " <" . $to . ">\nReply-To: " . $to;
			} else {
				$reheader .= "$to\nReply-To: " . $to;
			}
			$reheader .= "\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/" . phpversion();
			return $reheader;
		}
		//ユーザ宛送信メールボディ
		function mailToUser($arr, $dsp_name, $remail_text, $mailFooterDsp, $mailSignature, $encode)
		{
			$userBody = '';
			if (isset($arr[$dsp_name])) $userBody = h($arr[$dsp_name]) . " 様\n";
			$userBody .= $remail_text;
			$userBody .= "\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
			$userBody .= postToMail($arr); //POSTデータを関数からセット
			$userBody .= "\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
			$userBody .= "送信日時：" . date("Y/m/d (D) H:i:s", time()) . "\n";
			if ($mailFooterDsp == 1) $userBody .= $mailSignature;
			return mb_convert_encoding($userBody, "JIS", $encode);
		}
		//必須チェック関数
		function requireCheck($require)
		{
			$res['errm'] = '';
			$res['empty_flag'] = 0;
			foreach ($require as $requireVal) {
				$existsFalg = '';
				foreach ($_POST as $key => $val) {
					if ($key == $requireVal) {

						//連結指定の項目（配列）のための必須チェック
						if (is_array($val)) {
							$connectEmpty = 0;
							foreach ($val as $kk => $vv) {
								if (is_array($vv)) {
									foreach ($vv as $kk02 => $vv02) {
										if ($vv02 == '') {
											$connectEmpty++;
										}
									}
								}
							}
							if ($connectEmpty > 0) {
								$res['errm'] .= "<p class=\"error_messe\">【" . h($key) . "】は必須項目です。</p>\n";
								$res['empty_flag'] = 1;
							}
						}
						//デフォルト必須チェック
						elseif ($val == '') {
							$res['errm'] .= "<p class=\"error_messe\">【" . h($key) . "】は必須項目です。</p>\n";
							$res['empty_flag'] = 1;
						}

						$existsFalg = 1;
						break;
					}
				}
				if ($existsFalg != 1) {
					$res['errm'] .= "<p class=\"error_messe\">【" . $requireVal . "】が未選択です。</p>\n";
					$res['empty_flag'] = 1;
				}
			}

			return $res;
		}
		//リファラチェック
		function refererCheck($Referer_check, $Referer_check_domain)
		{
			if ($Referer_check == 1 && !empty($Referer_check_domain)) {
				if (strpos($_SERVER['HTTP_REFERER'], $Referer_check_domain) === false) {
					return exit('<p align="center">リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>');
				}
			}
		}
		function copyright()
		{
			echo '<a style="display:block;text-align:center;margin:15px 0;font-size:11px;color:#aaa;text-decoration:none" href="http://www.php-factory.net/" target="_blank">- PHP工房 -</a>';
		}
		//----------------------------------------------------------------------
		//  関数定義(END)
		//----------------------------------------------------------------------
?>
