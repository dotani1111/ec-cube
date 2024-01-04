import { test, expect } from '@playwright/test';

test('has shop name', async ({ page }) => {

  // goto localhost
  await page.goto('https://127.0.0.1:8000/');

  // Get the h1 element and expect it
  const h1Text = await page.innerText("h1");
  expect(h1Text).toBe("EC-CUBE SHOP");

  // Get a Screenshot
  await page.screenshot({ path: 'example.png' });

});

test('default display for Top page', async ({ page }) => {

  // goto localhost
  await page.goto('https://127.0.0.1:8000/');

  // カテゴリ選択ボックス（キーワード検索用）、キーワード検索入力欄、虫眼鏡ボタンが表示されている
  /* combobox（SelectBox）に含まれる要素を取得して、該当のワードが入っているのかをみている。*/
  await expect(page.getByRole('combobox')).toContainText('全ての商品');
  await expect(page.getByRole('combobox')).toHaveValue('');
  /* キーワード検索入力欄の表示チェック。*/
  await expect(page.getByRole('searchbox', { name: 'キーワードを入力' })).toBeVisible();
  /* 虫眼鏡（検索ボタン）の表示チェック。*/
  await expect(page.getByRole('banner').getByRole('button')).toBeVisible(); // 本家テストの虫眼鏡ボタンは何処へ...?

  // カテゴリ名（カテゴリ検索用）が表示されている
  /* カテゴリの配列を指定して、ループで回して検査する */
  const categories: string[] = ['全ての商品','新入荷','ジェラート','彩のデザート','CUBE','アイスサンド','フルーツ']
  for (const category of categories) {
    await expect(page.getByRole('combobox')).toContainText(category);
  }

  // 管理側のコンテンツ管理（新着情報管理）に設定されている情報が、順位順に表示されている
  /* 本家ではコードで登録・削除しているが、今回は事前に2件登録する方針
  それぞれ以下で登録済み
  title: test1    公開日時: 2023/12/19
  title: test2    公開日時: 2023/12/18
  */

  const news = await page.locator('.ec-newsRole__newsItem').all();
  for (const index of news) {
    await index.textContent();
    //console.log(await index.textContent());


  }
  console.log(await news[0].textContent());
});
