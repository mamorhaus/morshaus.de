document.addEventListener('DOMContentLoaded',function(){
    var button=document.querySelector('.mh-menu-toggle');
    var panel=document.querySelector('.mh-navigation-panel');
    if(!button||!panel)return;

    var title=document.querySelector('.mh-navigation-title');
    var separator=document.querySelector('.mh-navigation-separator');
    var activeLink=panel.querySelector('.mh-menu > li.current-menu-item > a, .mh-menu > li.current-menu-ancestor > a, .mh-menu > li.current-menu-parent > a, .mh-menu > li.current_page_item > a, .mh-menu > li.current_page_ancestor > a, .mh-menu > li.current_page_parent > a');
    if(title&&separator&&activeLink&&activeLink.textContent.trim()){
        title.textContent=activeLink.textContent.trim();
        title.hidden=false;
        separator.hidden=false;
    }

    var orderLink=document.querySelector('.mh-navigation-order');
    var orderCount=document.querySelector('.mh-navigation-order-count');
    var menuOrderLink=panel.querySelector('.mh-menu-order > a');
    var menuOrderCount=null;
    if(menuOrderLink){
        menuOrderCount=document.createElement('span');
        menuOrderCount.className='mh-menu-order-count';
        menuOrderCount.hidden=true;
        menuOrderCount.setAttribute('aria-hidden','true');
        menuOrderLink.appendChild(menuOrderCount);
    }
    function updateOrderCount(){
        if(!orderLink||!orderCount)return;
        try {
            var stored=window.localStorage.getItem('bestellliste');
            var list=stored===null?[]:JSON.parse(stored);
            if(!Array.isArray(list))throw new Error('Die gespeicherte Bestellliste hat ein ungültiges Format.');
            var count=list.reduce(function(total,item){
                var quantity=item&&Number(item.quantity);
                if(!Number.isSafeInteger(quantity)||quantity<1||!Number.isSafeInteger(total+quantity)){
                    throw new Error('Ein Eintrag der Bestellliste hat eine ungültige Anzahl.');
                }
                return total+quantity;
            },0);
            orderCount.textContent=count>99?'99+':String(count);
            orderCount.hidden=count===0;
            orderLink.setAttribute('aria-label',count?'Bestellliste, Gesamtanzahl: '+count:'Bestellliste');
            if(menuOrderCount){
                menuOrderCount.textContent=orderCount.textContent;
                menuOrderCount.hidden=count===0;
                menuOrderLink.setAttribute('aria-label',orderLink.getAttribute('aria-label'));
            }
        } catch(error) {
            orderCount.hidden=true;
            orderLink.setAttribute('aria-label','Bestellliste, Anzahl nicht verfügbar');
            if(menuOrderCount){
                menuOrderCount.hidden=true;
                menuOrderLink.setAttribute('aria-label',orderLink.getAttribute('aria-label'));
            }
            console.error(error);
        }
    }
    updateOrderCount();
    window.addEventListener('pageshow',updateOrderCount);
    window.addEventListener('mh:order-list-changed',updateOrderCount);
    window.addEventListener('storage',function(event){
        if(event.key==='bestellliste'||event.key===null)updateOrderCount();
    });

    button.addEventListener('click',function(){
        var open=button.getAttribute('aria-expanded')==='true';
        button.setAttribute('aria-expanded',String(!open));
        panel.classList.toggle('is-open',!open);
    });
});