/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */


// 入力欄の入力文字数を確認し、超えていれば背景色を変えて警告
function setInputLengthChecker(selector, maxLength){
  $(selector).on('input', function(){
    let length = $(selector).val().length;
    if(length > maxLength){
      $(selector).css('background-color','pink');
    } else {
      $(selector).css('background-color','');
    }
  });
}
