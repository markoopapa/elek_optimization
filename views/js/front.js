/**
 * Universal Performance & Accessibility Optimizer - Unified Version
 * @author Markoo & Elek
 */

(function() {
    // --- 1. SEGÉDFÜGGVÉNYEK ---
    function showMsg(m, isErr) {
        var o = document.getElementById("elek-confirm-overlay");
        if (!o) return;
        
        var box = o.querySelector(".elek-confirm-box");
        var btnYes = document.getElementById("elek-yes");
        var btnNo = document.getElementById("elek-no");
        
        // JAVÍTÁS: innerHTML-t használunk, hogy működjön a <br> sortörés
        // TÖRÖLTÜK a sort, ami felülírta a szöveget a régi 'onlyStock'-ra
        box.querySelector("h4").innerHTML = m;
        
        if (isErr) { 
            // HIBA ESETÉN (Készlet):
            // A "Yes" gombot elrejtjük
            btnYes.style.display = "none"; 
            
            // A "No" gombból csinálunk "OK" gombot, és zöldre színezzük (elek-btn-yes stílus)
            btnNo.innerText = (typeof elek_t !== 'undefined' && elek_t.ok) ? elek_t.ok : 'OK';
            btnNo.className = "elek-btn-yes"; 
            
            // Ha rákattint, csak zárja be az ablakot
            btnNo.onclick = function(e) { e.preventDefault(); o.style.display = "none"; };
        } 
        else { 
            // KÉRDÉS ESETÉN (Kosár ürítés):
            // Látszik a "Yes" gomb (link)
            btnYes.style.display = "inline-block"; 
            
            // A "No" gomb szürke marad
            btnNo.innerText = (typeof elek_t !== 'undefined' && elek_t.no) ? elek_t.no : 'NO';
            btnNo.className = "elek-btn-no";
            
            // Bezárás funkció
            btnNo.onclick = function(e) { e.preventDefault(); o.style.display = "none"; };
        }
        
        o.style.display = "flex";
    }

    function setupBaseUI() {
        if (!document.getElementById("elek-confirm-overlay") && typeof elek_t !== 'undefined') {
            var c = document.createElement("div"); c.id = "elek-confirm-overlay";
            // Létrehozzuk az alap HTML szerkezetet
            c.innerHTML = '<div class="elek-confirm-box"><h4></h4><div class="elek-confirm-btns"><button class="elek-btn-no" id="elek-no"></button><a href="#" class="elek-btn-yes" id="elek-yes">' + elek_t.yes + '</a></div></div>';
            document.body.appendChild(c);
        }
    }

    // --- 2. DINAMIKUS FUNKCIÓK (EGYETLEN RUN FÜGGVÉNY!) ---
    function run() {
        if (typeof elek_cfg === 'undefined') return;
        setupBaseUI();
		
		if (parseInt(elek_cfg.checkout_selector) === 1) {
        document.body.classList.add('elek-optimizer-on');
        }
		
		// --- ÚJ: Osztály hozzáadása a BODY-hoz a CSS-nek ---
        var isEnabled = parseInt(elek_cfg.checkout_selector) === 1;
        document.body.classList.toggle('elek-optimizer-on', isEnabled)
		
		// --- MEGSZÓLÍTÁS (id_gender) ELREJTÉSE ---
        // Megkeressük a konkrét inputot a neve alapján
        var genderInput = document.querySelector('input[name="id_gender"]');
        if (genderInput) {
            // Megkeressük a legközelebbi szülő konténert, ami az egész sort tartja
            var row = genderInput.closest('.form-group');
            if (row) {
                if (parseInt(elek_cfg.checkout_selector) === 1) {
                    row.style.setProperty('display', 'none', 'important');
                } else {
                    row.style.display = 'flex';
                }
            }
        }

        // --- A. KOSÁR ÜRÍTÉSE GOMB ---
        if (elek_cfg.empty_cart) {
            var cr = document.querySelector("#js-cart-sidebar .cart-products-count");
            if (cr && !document.querySelector(".elek-empty-cart-btn")) {
                var d = document.createElement("div"); d.className = "elek-empty-cart-btn";
                d.innerHTML = '<a href="#" class="modern-empty-trigger">' + elek_t.emptyBtn + '</a>';
                d.querySelector(".modern-empty-trigger").onclick = function(e) {
                    e.preventDefault();
                    document.getElementById("elek-yes").href = elek_cfg.empty_url;
                    showMsg(elek_t.emptyQ, false);
                };
                cr.parentNode.insertBefore(d, cr.nextSibling);
            }
        }

        // --- B. LISTA MENNYISÉG (JAVÍTOTT MATEK: NINCS DUPLA SZÁMOLÁS) ---
        if (typeof elek_cfg !== 'undefined' && elek_cfg.list_qty) {
            console.log("--- ElekOptimizer: Smart Stock Mode (Fix Math) ---");

            document.querySelectorAll(".js-product-miniature, .product-miniature").forEach(function(card) {
                if (card.querySelector(".elek-list-qty")) return;

                var anyBtn = card.querySelector(".add-to-cart, .ajax_add_to_cart_button, button[type='submit']");
                var parent = anyBtn ? anyBtn.parentElement : null;

                if (anyBtn && parent) {
                    
                    // KOSÁR KERESŐ
                    var getCartQty = function(targetID) {
                        if (!targetID) return 0;
                        if (typeof prestashop !== 'undefined' && prestashop.cart && Array.isArray(prestashop.cart.products)) {
                            var totalFound = 0;
                            prestashop.cart.products.forEach(function(p) {
                                if (String(p.id_product) === String(targetID)) {
                                    totalFound += parseInt(p.cart_quantity);
                                }
                            });
                            return totalFound;
                        }
                        return 0;
                    };

                    var w = document.createElement("div"); w.className = "elek-list-qty";
                    w.innerHTML = '<button type="button" class="btn-m">-</button>' +
                                  '<input type="number" class="list-qty-input" value="1" min="1">' +
                                  '<button type="button" class="btn-p">+</button>';

                    try {
                        parent.insertBefore(w, anyBtn);
                        card.classList.add("elek-ready");
                        var mainSec = card.querySelector(".buttons-sections");
                        if (mainSec) mainSec.classList.add("elek-container-active");
                    } catch (err) { return; }

                    var inp = w.querySelector(".list-qty-input");

                    anyBtn.addEventListener("click", function(e) {
                        var q = parseInt(inp.value);
                        if (q < 1) return;

                        e.preventDefault(); e.stopImmediatePropagation();
                        
                        var finalID = anyBtn.getAttribute("data-id-product") || card.getAttribute("data-id-product");
                        if (!finalID) {
                             var i = card.querySelector("input[name='id_product']");
                             if(i) finalID = i.value;
                        }

                        var fd = new FormData();
                        fd.append("token", prestashop.static_token);
                        fd.append("id_product", finalID);
                        fd.append("qty", q); 
                        fd.append("add", 1); 
                        fd.append("action", "update");

                        fetch(prestashop.urls.pages.cart, { method: "POST", body: fd, headers: { "Accept": "application/json" } })
                        .then(function(r){ return r.json(); }).then(function(data) {
                            if (data.success) {
                                prestashop.emit("updateCart", { reason: { linkAction: "add-to-cart" }, resp: data });
                            } else {
                                // --- HIBAKEZELÉS ---
                                inp.value = 1;

                                var inCart = getCartQty(finalID);
                                var serverMsg = data.errors && data.errors.length > 0 ? data.errors[0] : "";
                                var matches = serverMsg.match(/(\d+)/); 
                                
                                var t_total = (typeof elek_t !== 'undefined' && elek_t.msgTotalStock) ? elek_t.msgTotalStock : "%s items in stock total";
                                var t_cart  = (typeof elek_t !== 'undefined' && elek_t.msgInCart) ? elek_t.msgInCart : " (of which %s are already in your cart)";
                                var t_none  = (typeof elek_t !== 'undefined' && elek_t.msgNoStock) ? elek_t.msgNoStock : "Not enough stock";

                                var customMsg = "";

                                if (matches) {
                                    // ITT A JAVÍTÁS: Nem adjuk hozzá a kosarat, mert a hibaüzenet már a teljes készletet mondja!
                                    var totalStock = parseInt(matches[0]); 
                                    
                                    // Ha véletlenül a hibaüzenet mégis kisebb lenne mint a kosár (pl. "még 2 maradt"), 
                                    // csak akkor adnánk össze, de a te esetedben a TOTAL jön vissza.
                                    // Ezért simán használjuk a matches[0]-t.
                                    customMsg = t_total.replace("%s", totalStock);
                                } else {
                                    customMsg = t_none;
                                }

                                if (inCart > 0) {
                                    customMsg += "<br><span style='font-size: 0.9em; font-weight: normal; opacity: 0.8;'>" + t_cart.replace("%s", inCart) + "</span>";
                                } else {
                                    customMsg += ".";
                                }

                                showMsg(customMsg, true); 
                            }
                        });
                    }, true);

                    w.querySelector(".btn-p").onclick = function(e){ e.preventDefault(); inp.value = parseInt(inp.value) + 1; };
                    w.querySelector(".btn-m").onclick = function(e){ e.preventDefault(); if(parseInt(inp.value) > 1) inp.value = parseInt(inp.value) - 1; };
                }
            });
        }

        // --- C. CHECKOUT CÉGVÁLASZTÓ LOGIKA ---
        var addressStep = document.querySelector('.step-part[data-step-part-id="checkout-addresses-step"]');
        var isAddressCurrent = addressStep && addressStep.classList.contains('current');
        var addressForm = document.querySelector('#address-form form, .js-address-form form');

        // 1. Ha KI van kapcsolva vagy nem a 2. lépésnél vagyunk -> Törlés és alaphelyzet
        if (!elek_cfg.checkout_selector || !isAddressCurrent) {
            var old = document.querySelector('.elek-client-type-selector');
            if (old) {
                old.remove();
                if(addressForm) {
                    addressForm.classList.remove('elek-selection-made'); // Töröljük a "választás kész" jelet
                    addressForm.querySelectorAll('.form-group').forEach(function(r) { r.style.display = 'flex'; });
                }
            }
        } 
        // 2. Ha BE van kapcsolva és a 2. lépésnél vagyunk -> Választó létrehozása
        else if (addressForm && !document.querySelector('.elek-client-type-selector')) {
            
            var sel = document.createElement('div');
            sel.className = 'elek-client-type-selector';
            sel.style = "display:flex !important; flex-direction:column !important; align-items:center !important; width:100% !important; margin:15px 0 !important; padding:15px !important; background:#f4f8fb !important; border:1px solid #d3e0e9 !important; border-radius:8px !important;";
            sel.innerHTML = `<div style="text-align:center; width:100%; font-weight:bold; margin-bottom:10px;">${elek_t.clientType}</div>
                <div class="elek-choices-wrapper" style="display:flex; gap:10px; justify-content:center; width:100%;">
                    <div class="elek-card" data-v="f" style="flex:1; cursor:pointer; padding:12px; border:2px solid #ccc; border-radius:8px; text-align:center; font-weight:bold; background:#fff;">${elek_t.fizica}</div>
                    <div class="elek-card" data-v="j" style="flex:1; cursor:pointer; padding:12px; border:2px solid #ccc; border-radius:8px; text-align:center; font-weight:bold; background:#fff;">${elek_t.juridica}</div>
                </div>`;
            
            addressForm.insertBefore(sel, addressForm.firstChild);

            // Itt definiáljuk a függvényt, amit kerestél:
            var updateVisibility = function(val) {
    if (val && addressForm) {
        addressForm.classList.add('elek-selection-made');
    }

    // 1. Gombok színezése
    document.querySelectorAll('.elek-card').forEach(function(c) {
        var act = c.getAttribute('data-v') === val;
        c.style.setProperty('border-color', act ? 'var(--st-theme-color, #B20301)' : '#ccc', 'important');
        c.style.setProperty('background', act ? '#fff5f5' : '#fff', 'important');
        c.style.setProperty('color', act ? 'var(--st-theme-color, #B20301)' : '#333', 'important');
    });

    // 2. Mezők szűrése
    var allRows = addressForm.querySelectorAll('.form-group');
    var isJur = (val === 'j');

    allRows.forEach(function(row) {
        // A választó gombokat soha ne bántsuk
        if (row.classList.contains('elek-client-type-selector')) return;

        var input = row.querySelector('input, select, textarea');
        var name = input ? (input.getAttribute('name') || "").toLowerCase() : "";
        var classes = row.className.toLowerCase();

        // Kibővített céges mező figyelés (ST-Theme kompatibilis)
        var isComp = classes.includes('company') || name.includes('company') || 
                     classes.includes('vat_number') || name.includes('vat_number') || 
                     classes.includes('dni') || name.includes('dni') ||
                     classes.includes('invoice_info');

        if (!val) {
            // Amíg nincs választás, minden rejtve
            row.style.setProperty('display', 'none', 'important');
        } else {
            if (isComp && !isJur) {
                // Magánszemélynél a céges mezőket eldugjuk
                row.style.setProperty('display', 'none', 'important');
            } else {
                // Minden mást mutatunk
                row.style.setProperty('display', 'flex', 'important');
            }
        }
    });
};

            // Kattintás események hozzárendelése
            document.querySelectorAll('.elek-card').forEach(function(card) {
                card.onclick = function() { updateVisibility(this.getAttribute('data-v')); };
            });

            updateVisibility(""); // Indításkor minden rejtve
        }
    }

    // --- 3. EGYSZERI FIXEK (DOM READY) ---
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof elek_cfg !== 'undefined' && elek_cfg.heading_fix) {
            document.querySelectorAll('h5.product-name').forEach(el => {
                const h2 = document.createElement('h2');
                h2.innerHTML = el.innerHTML; h2.className = el.className;
                el.parentNode.replaceChild(h2, el);
            });
        }

        const runSlick = () => {
            if (window.jQuery && window.jQuery.fn.slick) {
                $('.slick-slider').on('afterChange init', function() {
                    $(this).find('.slick-slide[aria-hidden="true"] a').attr('tabindex', '-1');
                    $(this).find('.slick-slide[aria-hidden="false"] a').attr('tabindex', '0');
                }).find('.slick-slide[aria-hidden="true"] a').attr('tabindex', '-1');
            }
        };
        let checkJ = setInterval(function() { if (window.jQuery) { clearInterval(checkJ); runSlick(); } }, 100);
    });

    // Motor indítása
	run();
    setInterval(run, 1000);
})();
