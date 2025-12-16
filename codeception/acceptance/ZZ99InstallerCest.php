<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Page\Install\InstallPage;

/**
 * @group installer
 */
class ZZ99InstallerCest
{
    protected $writableFiles = [
        'app/Plugin',
        'app/PluginData',
        'app/proxy',
        'app/template',
        'html',
        'var',
        'vendor',
        'composer.json',
        'composer.lock',
    ];

    /**
     * JavaScriptエラーをチェックする.
     */
    protected function checkJavaScriptErrors(AcceptanceTester $I)
    {
        try {
            // executeInSeleniumを使ってWebDriverインスタンスにアクセス
            $errors = [];
            $I->executeInSelenium(function (Facebook\WebDriver\Remote\RemoteWebDriver $webDriver) use (&$errors) {
                try {
                    $logs = $webDriver->manage()->getLog('browser');
                    foreach ($logs as $log) {
                        $level = $log['level'] ?? '';
                        // SEVERE はエラー、WARNING は警告として扱う
                        if ($level === 'SEVERE' || $level === 'WARNING') {
                            $message = $log['message'] ?? '';
                            $errors[] = "[{$level}] {$message}";
                        }
                    }
                } catch (Exception $e) {
                    // getLogが利用できない場合（一部のブラウザではサポートされていない）
                }
            });

            if (!empty($errors)) {
                $I->comment('JavaScriptエラー/警告が検出されました:');
                foreach ($errors as $error) {
                    $I->comment("  - {$error}");
                }
            } else {
                $I->comment('JavaScriptエラーは検出されませんでした');
            }
        } catch (Exception $e) {
            // ブラウザログの取得に失敗した場合はスキップ
            $I->comment("ブラウザログの取得に失敗しました: {$e->getMessage()}");
        }
    }

    /**
     * 権限チェックのテスト.
     */
    public function installer_CheckPermission(AcceptanceTester $I)
    {
        $I->wantTo('ZZ99 インストーラ 権限チェックのテスト');

        // step1
        $page = InstallPage::go($I);
        $I->see('ようこそ', InstallPage::$STEP1_タイトル);
        $currentUrl = $I->executeJS('return location.href');
        $I->comment("Step1 現在のURL: {$currentUrl}");

        // JavaScriptエラーをチェック
        $this->checkJavaScriptErrors($I);

        // 次へ
        $page->step1_次へボタンをクリック();
        $I->comment('Step1 次へボタンをクリックしました');

        // フォーム送信後のJavaScriptエラーをチェック（遷移前にチェック）
        $this->checkJavaScriptErrors($I);

        // step2への遷移を待つ（ループで待機）
        $I->comment('Step2への遷移を待機中...');
        $maxAttempts = 20; // 最大20回試行（5秒 × 20 = 100秒）
        $attempt = 0;
        $migrated = false;

        while ($attempt < $maxAttempts && !$migrated) {
            try {
                $currentPath = $I->executeJS('return location.pathname + location.search');
                $I->comment("試行 {$attempt}: 現在のパス: {$currentPath}");

                if ($currentPath === '/install/step2') {
                    $migrated = true;
                    $I->comment('Step2への遷移が確認されました');
                    break;
                }

                // まだ遷移していない場合は5秒待機
                if ($attempt < $maxAttempts - 1) {
                    $I->wait(5);
                }
                $attempt++;
            } catch (Exception $e) {
                $I->comment("遷移確認中にエラーが発生しました: {$e->getMessage()}");
                $I->wait(5);
                $attempt++;
            }
        }

        if (!$migrated) {
            // 遷移に失敗した場合、現在の状態を確認
            $e = new Exception('Step2への遷移がタイムアウトしました');
            $currentUrl = $I->executeJS('return location.href');
            $I->comment("Step2への遷移に失敗しました。現在のURL: {$currentUrl}");

            // ページの状態を確認
            $pageTitle = $I->executeJS('return document.title');
            $I->comment("ページタイトル: {$pageTitle}");

            // エラーメッセージが表示されていないか確認
            try {
                $pageSource = $I->grabPageSource();

                // フォームの存在を確認
                if (strpos($pageSource, 'form1') !== false) {
                    $I->comment('フォーム#form1が存在します');
                } else {
                    $I->comment('フォーム#form1が見つかりません');
                }

                // エラーメッセージを確認
                if (strpos($pageSource, 'alert-danger') !== false) {
                    try {
                        $alertText = $I->grabTextFrom('.alert-danger');
                        $I->comment("エラーメッセージ: {$alertText}");
                    } catch (Exception $e3) {
                        $I->comment('エラーメッセージ要素は存在しますが、テキスト取得に失敗しました');
                    }
                }
                if (strpos($pageSource, 'alert-warning') !== false) {
                    try {
                        $alertText = $I->grabTextFrom('.alert-warning');
                        $I->comment("警告メッセージ: {$alertText}");
                    } catch (Exception $e3) {
                        $I->comment('警告メッセージ要素は存在しますが、テキスト取得に失敗しました');
                    }
                }

                // フォームエラーを確認
                if (strpos($pageSource, 'form-error') !== false || strpos($pageSource, 'has-error') !== false) {
                    $I->comment('フォームエラーが検出されました');
                }

                // フォームの状態を確認（CSRFトークンなど）
                if (strpos($pageSource, '_token') !== false) {
                    $I->comment('CSRFトークンが存在します');
                } else {
                    $I->comment('CSRFトークンが見つかりません');
                }

                // ページソースの一部を出力（デバッグ用）
                $pageSourceLength = strlen($pageSource);
                $I->comment("ページソースのサイズ: {$pageSourceLength} bytes");
                if ($pageSourceLength > 0) {
                    $preview = substr($pageSource, 0, 500);
                    $I->comment("ページソースの先頭500文字: {$preview}...");
                }
            } catch (Exception $e2) {
                $I->comment("ページソース取得エラー: {$e2->getMessage()}");
            }

            // 再度JavaScriptエラーをチェック
            $this->checkJavaScriptErrors($I);

            // エラーを再スローしてテストを失敗させる
            throw $e;
        }

        $currentUrl = $I->executeJS('return location.href');
        $I->comment("遷移完了後のURL: {$currentUrl}");

        $I->waitForElementVisible(InstallPage::$STEP2_タイトル, 10);
        $I->comment('Step2タイトル要素が表示されました');

        $I->waitForText('権限チェック', 10, InstallPage::$STEP2_タイトル);
        $I->comment('権限チェックテキストを確認しました');

        $I->see('権限チェック', InstallPage::$STEP2_タイトル);
        $I->see('アクセス権限は正常です', InstallPage::$STEP2_テキストエリア);
        $I->comment('Step2の確認が完了しました');

        // Step2遷移後のJavaScriptエラーをチェック
        $this->checkJavaScriptErrors($I);

        $rootDir = __DIR__.'/../../';

        foreach ($this->writableFiles as $file) {
            $path = $rootDir.$file;
            $origin = octdec(substr(sprintf('%o', fileperms($path)), -4));

            // 書き込み権限を外す
            chmod($path, 0555);

            // リロードしてエラーが表示されることを確認.
            $page->step2_リロード();
            $I->see('以下のファイルまたはディレクトリに書き込み権限を付与してください', InstallPage::$STEP2_テキストエリア);
            $I->see($file, InstallPage::$STEP2_テキストエリア);

            // リロード後のJavaScriptエラーをチェック
            $this->checkJavaScriptErrors($I);

            // 権限を戻す.
            chmod($path, $origin);
        }

        // 対象外のディレクトリ・ファイルの確認
        $externalDir = $rootDir.'externalDir';
        mkdir($externalDir, 0555, true);

        $externalFile = $rootDir.'externalFile.txt';
        touch($externalFile);
        chmod($externalFile, 0555);

        $page->step2_リロード();
        $I->see('アクセス権限は正常です', InstallPage::$STEP2_テキストエリア);

        // 最終リロード後のJavaScriptエラーをチェック
        $this->checkJavaScriptErrors($I);

        chmod($externalDir, 0777);
        chmod($externalFile, 0777);
        rmdir($externalDir);
        unlink($externalFile);
    }
}
