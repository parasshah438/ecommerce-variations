// Enhanced Virtual Try-On Cart Integration
function addToCartFixed() {
    // Check if user is authenticated
    if (!isAuthenticated()) {
        showToast('Please login to add items to cart', 'warning');
        window.location.href = '/login';
        return;
    }
    
    // Get selected product data
    const productData = {
        product_id: getSelectedProductId(),
        variation_id: getSelectedVariationId(),
        quantity: 1,
        size: selectedSize,
        color: selectedColor
    };
    
    // Make AJAX request to add to cart
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(productData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Added ${selectedProduct} (${selectedSize}) to cart!`, 'success');
            updateCartCount(data.cart_count);
            
            // Animate button
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-2"></i>Added to Cart';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    });
}

function getSelectedProductId() {
    // Map demo products to real database IDs
    const productMapping = {
        'T-Shirt': 1,
        'Hoodie': 2,
        'Jacket': 3,
        'Dress': 4
    };
    return productMapping[selectedProduct] || 1;
}

function getSelectedVariationId() {
    // This would need to query the actual variations based on size/color
    // For now, return a default variation ID
    return 1;
}

function isAuthenticated() {
    // Check if user is logged in (you can implement this based on your auth system)
    return document.querySelector('meta[name="user-authenticated"]')?.getAttribute('content') === 'true';
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}