// ===========================
// HANDLE ADD TO CART - MENU FAVORIT
// ===========================
async function handleAddToCart(menuId, menuName, price, category) {
    try {
        // Cek apakah kategori Seblak (butuh pilih level)
        const isSeblak = category.toLowerCase().includes('seblak');

        if (isSeblak) {
            // Buka popup level untuk Seblak
            showLevelPopup(menuId, menuName, price);
        } else {
            // Langsung tambah ke cart untuk non-seblak
            await addToCart(menuId, menuName, price, null);
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Gagal menambahkan ke keranjang!', 'error');
    }
}

// ===== Add to Cart Function =====
async function addToCart(menuId, menuName, price, level = null) {
    try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('menu_id', menuId);
        formData.append('quantity', 1);

        // Tambahkan level jika ada (untuk Seblak)
        if (level !== null) {
            formData.append('level', level);
        }

        const response = await fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Tampilkan pesan dengan info level jika ada
            let message = `✓ ${menuName} ditambahkan ke keranjang!`;
            if (level !== null) {
                message = `✓ ${menuName} (Level ${level}) ditambahkan ke keranjang!`;
            }
            showNotification(message, 'success');
            updateCartBadge(result.cart_count);
        } else {
            if (result.redirect) {
                // User belum login, redirect ke halaman login/register
                if (confirm(result.message + '\n\nApakah Anda ingin login sekarang?')) {
                    window.location.href = result.redirect;
                }
            } else {
                showNotification(result.message, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Gagal menambahkan ke keranjang', 'error');
    }
}

// ===== Update Cart Badge =====
function updateCartBadge(count) {
    let badge = document.querySelector('.cart-badge');

    if (!badge && count > 0) {
        // Create badge if not exists
        const cartLink = document.querySelector('a[href="cart.html"], a[href="cart.php"]');
        if (cartLink) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.style.cssText = `
                position: absolute;
                top: -8px;
                right: -8px;
                background: #ff4b4b;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                font-weight: 700;
            `;
            cartLink.style.position = 'relative';
            cartLink.appendChild(badge);
        }
    }

    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

// ===== Load Cart Count on Page Load =====
async function loadCartCount() {
    try {
        const formData = new FormData();
        formData.append('action', 'count');

        const response = await fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            updateCartBadge(result.count);
        }
    } catch (error) {
        console.error('Error loading cart count:', error);
    }
}

// Load cart count when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCartCount();
});

// ===== Notification Function =====
function showNotification(message, type = 'success') {
    // Hapus notifikasi lama jika ada
    const oldNotif = document.querySelector('.custom-notification');
    if (oldNotif) oldNotif.remove();

    const notification = document.createElement('div');
    notification.className = 'custom-notification';
    notification.textContent = message;

    const bgColor = type === 'success' ? '#43a047' : '#ff4b4b';

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: ${bgColor};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ===========================
// POPUP LEVEL PEDAS - SEBLAK
// ===========================
let currentMenuData = null;
let selectedLevel = null;

function showLevelPopup(menuId, menuName, price) {
    currentMenuData = {
        menuId,
        menuName,
        price
    };

    // Buat popup level dinamis
    const popupHTML = `
        <div class="level-popup-overlay" id="levelPopupOverlay">
            <div class="level-popup">
                <div class="level-popup-header">
                    <div class="level-popup-icon">🌶️</div>
                    <h3 class="level-popup-title">Pilih Tingkat Kepedasan</h3>
                    <p class="level-popup-subtitle">Seberapa pedas kamu mau?</p>
                </div>

                <div class="level-options">
                    <div class="level-option" onclick="selectLevel(0)">
                        <span class="level-option-icon">😊</span>
                        <p class="level-option-name">Level 0</p>
                        <p class="level-option-desc">Tidak Pedas</p>
                    </div>
                    <div class="level-option" onclick="selectLevel(1)">
                        <span class="level-option-icon">🙂</span>
                        <p class="level-option-name">Level 1</p>
                        <p class="level-option-desc">Sedikit Pedas</p>
                    </div>
                    <div class="level-option" onclick="selectLevel(2)">
                        <span class="level-option-icon">😋</span>
                        <p class="level-option-name">Level 2</p>
                        <p class="level-option-desc">Sedang</p>
                    </div>
                    <div class="level-option" onclick="selectLevel(3)">
                        <span class="level-option-icon">😅</span>
                        <p class="level-option-name">Level 3</p>
                        <p class="level-option-desc">Pedas</p>
                    </div>
                    <div class="level-option" onclick="selectLevel(4)">
                        <span class="level-option-icon">🥵</span>
                        <p class="level-option-name">Level 4</p>
                        <p class="level-option-desc">Sangat Pedas</p>
                    </div>
                    <div class="level-option" onclick="selectLevel(5)">
                        <span class="level-option-icon">💀</span>
                        <p class="level-option-name">Level 5</p>
                        <p class="level-option-desc">Extra Pedas!</p>
                    </div>
                </div>

                <div class="level-popup-footer">
                    <button class="level-btn level-btn-cancel" onclick="closeLevelPopup()">Batal</button>
                    <button class="level-btn level-btn-confirm" id="btnConfirmLevel" disabled onclick="confirmLevel()">Tambahkan</button>
                </div>
            </div>
        </div>
    `;

    // Tambahkan ke body
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = popupHTML;
    document.body.appendChild(tempDiv.firstElementChild);

    // Tampilkan popup
    setTimeout(() => {
        document.getElementById('levelPopupOverlay').classList.add('active');
    }, 10);
    document.body.style.overflow = 'hidden';
}

function selectLevel(level) {
    selectedLevel = level;

    // Remove selected class from all
    document.querySelectorAll('.level-option').forEach(opt => {
        opt.classList.remove('selected');
    });

    // Add selected to clicked
    event.target.closest('.level-option').classList.add('selected');

    // Enable confirm button
    document.getElementById('btnConfirmLevel').disabled = false;
}

function closeLevelPopup() {
    const popup = document.getElementById('levelPopupOverlay');
    if (popup) {
        popup.classList.remove('active');
        setTimeout(() => {
            popup.remove();
        }, 300);
        document.body.style.overflow = '';
        selectedLevel = null;
        currentMenuData = null;
    }
}

async function confirmLevel() {
    if (selectedLevel === null || !currentMenuData) return;

    const tempData = {...currentMenuData};
    const tempLevel = selectedLevel;
    
    closeLevelPopup();

    await addToCart(
        tempData.menuId,
        tempData.menuName,
        tempData.price,
        tempLevel
    );
}

// Close popup saat klik overlay
document.addEventListener('click', function(e) {
    if (e.target.id === 'levelPopupOverlay') {
        closeLevelPopup();
    }
});

// Add Level Popup Styles
const levelStyle = document.createElement('style');
levelStyle.textContent = `
    .level-popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(3px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .level-popup-overlay.active {
        opacity: 1;
    }

    .level-popup {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        position: relative;
    }

    .level-popup-overlay.active .level-popup {
        transform: scale(1);
    }

    .level-popup-header {
        text-align: center;
        margin-bottom: 1.2rem;
    }

    .level-popup-icon {
        font-size: 2.5rem;
        margin-bottom: 0.3rem;
    }

    .level-popup-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.3rem;
    }

    .level-popup-subtitle {
        color: #999;
        font-size: 0.85rem;
    }

    .level-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.7rem;
        margin-bottom: 1.2rem;
    }

    .level-option {
        border: 2px solid #e5e5e5;
        border-radius: 10px;
        padding: 0.8rem 0.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }

    .level-option:hover {
        border-color: #bf0f0f;
        background: #fff8f8;
    }

    .level-option.selected {
        border-color: #bf0f0f;
        background: #ffebeb;
        box-shadow: 0 2px 8px rgba(191, 15, 15, 0.2);
    }

    .level-option-icon {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.3rem;
    }

    .level-option-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.15rem;
        font-size: 0.9rem;
    }

    .level-option-desc {
        font-size: 0.75rem;
        color: #999;
    }

    .level-popup-footer {
        display: flex;
        gap: 0.7rem;
    }

    .level-btn {
        flex: 1;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .level-btn-cancel {
        background: #f5f5f5;
        color: #666;
    }

    .level-btn-cancel:hover {
        background: #e5e5e5;
    }

    .level-btn-confirm {
        background: #bf0f0f;
        color: white;
    }

    .level-btn-confirm:hover:not(:disabled) {
        background: #a00d0d;
    }

    .level-btn-confirm:disabled {
        background: #ddd;
        cursor: not-allowed;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .level-popup {
            max-width: 360px;
            padding: 1.3rem;
        }

        .level-options {
            grid-template-columns: repeat(3, 1fr);
            gap: 0.6rem;
        }

        .level-option {
            padding: 0.7rem 0.4rem;
        }

        .level-option-icon {
            font-size: 1.8rem;
        }

        .level-option-name {
            font-size: 0.85rem;
        }

        .level-option-desc {
            font-size: 0.7rem;
        }
    }
`;
document.head.appendChild(levelStyle);