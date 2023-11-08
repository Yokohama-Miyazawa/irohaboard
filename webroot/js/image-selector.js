/*!
 * image-selector.js v1.0
 *
 * Copyright (c) 2023 motsu
 *
 * Released under the MIT license.
 * see https://opensource.org/licenses/MIT
 */
class ImageSelector{
  #el_pulldown;
  #els_option;
  #el_preview;
  #funcOnChange;
  constructor(el_parent){
    if(!el_parent instanceof HTMLElement) return undefined;
    el_parent.classList.add('ImageSelector');
    this.#els_option = el_parent.getElementsByClassName('ImageSelector__option');
    if(this.#els_option.length<1) return undefined;
    //プルダウン作成
    this.#el_pulldown = document.createElement('div');
    this.#el_pulldown.classList.add('ImageSelector__pulldown');
    //選択中要素+アイコンスペース
    const el_display = document.createElement('div');
    el_display.classList.add('ImageSelector__display')
    el_display.addEventListener('click',()=>{
      this.#el_pulldown.classList.toggle('ImageSelector__s-spread');
    })
    el_parent.appendChild(el_display);
    //選択中要素欄
    this.#el_preview = document.createElement('div');
    this.#el_preview.classList.add('ImageSelector__preview')
    const els_selected = el_parent.getElementsByClassName('ImageSelector__s-selected');
    if(els_selected.length===0) this.#els_option[0].classList.add('ImageSelector__s-selected');
    const el_clone_option_first = (els_selected.length===0) ? this.#els_option[0].cloneNode(true) : els_selected[0].cloneNode(true);
    el_clone_option_first.classList.add('ImageSelector__preview-child');
    el_clone_option_first.classList.remove('ImageSelector__option');
    el_clone_option_first.classList.remove('ImageSelector__s-selected');
    this.value = el_clone_option_first.dataset.value;
    this.#el_preview.appendChild(el_clone_option_first);
    el_display.appendChild(this.#el_preview);
    //プルダウン追加
    el_parent.appendChild(this.#el_pulldown);
    [...this.#els_option].forEach(el_option=>{
      el_option.addEventListener('click',()=>{
        this.#selectOption(el_option);
      });
      this.#el_pulldown.appendChild(el_option);
    });
    //セレクトアイコン追加
    const el_icon = document.createElement('div');
    el_icon.classList.add('ImageSelector__icon');
    el_display.appendChild(el_icon);
    //外部クリック処理
    const css_celector = (el_parent.id==='') ? '.ImageSelector' : '#'+el_parent.id;
    window.addEventListener('click',(e)=>{
      if(e.target.closest(css_celector)===null) this.#el_pulldown.classList.remove('ImageSelector__s-spread');
    });
    //
  }
  #selectOption(el_option){
    //s-selected処理
    [...this.#els_option].forEach(el=>{
      el.classList.remove('ImageSelector__s-selected');
    });
    el_option.classList.add('ImageSelector__s-selected');
    //表示要素作成
    const el_clone_option = el_option.cloneNode(true);
    el_clone_option.classList.add('ImageSelector__preview-child');
    el_clone_option.classList.remove('ImageSelector__option');
    el_clone_option.classList.remove('ImageSelector__s-selected');
    this.#el_preview.textContent = '';
    this.#el_preview.appendChild(el_clone_option);
    //
    this.value = el_option.dataset.value;
    this.#el_pulldown.classList.remove('ImageSelector__s-spread');
    this.onChange();
  }
  setHeight(num){
    if(Number.isInteger(num)&&num>0) this.#el_pulldown.style.maxHeight = num +'px';
  }
  setValue(val){
    for(let i=0;i<this.#els_option.length;i++){
      if(this.#els_option[i].dataset.value===val){
        this.#selectOption(this.#els_option[i]);
        break;
      }
    }
  }
  setOnChange(func){
    this.#funcOnChange = func;
  }
  onChange(){
    if(typeof this.#funcOnChange === 'function') this.#funcOnChange();
  }
}