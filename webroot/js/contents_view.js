/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

var _studySec = 0;

$(document).ready(function () {
  setInterval("_studySec++;", 1000);
});

// 学習終了
function finish(val) {
  // 学習履歴の重複記録防止の為、ボタンを無効化
  $(".btn").prop("disabled", true);

  // プレビューの場合、学習履歴を保存しない
  if (
    location.href.split("/")[location.href.split("/").length - 1] == "preview"
  ) {
    window.close();
    return;
  }

  // 中断の場合
  if (val == 0) {
    location.href = URL_RECORDS_ADD + "/0/" + _studySec + "/0";
    return;
  }

  // 学習履歴を残さずに終了の場合
  if (val == -1) {
    location.href = URL_CONTNES_INDEX;
    return;
  }

  // 学習履歴を残して終了の場合
  location.href = URL_RECORDS_ADD + "/1/" + _studySec + "/" + val;
  return;
}
