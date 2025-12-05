console.log("189 Beauty Loaded");

// PRODUCT FILTERING
function filterProducts(category) {
    const items = document.querySelectorAll(".product-card");

    items.forEach(item => {
        if (category === "all" || item.dataset.category === category) {
            item.style.display = "block";
        } else {
            item.style.display = "none";
        }
    });
}
function openModal(name, price, img, desc) {
    document.getElementById("modalImg").src = img;
    document.getElementById("modalName").innerText = name;
    document.getElementById("modalPrice").innerText = "$" + price;
    document.getElementById("modalDesc").innerText = desc;

    document.getElementById("modalNameField").value = name;
    document.getElementById("modalPriceField").value = price;
    document.getElementById("modalImgField").value = img;

    document.getElementById("productModal").style.display = "flex";

 // DEBUG: Check what price is being passed
    console.log("Setting price:", price);
    
    document.getElementById("modal-id").value = id;
    document.getElementById("modal-name-input").value = name;
    document.getElementById("modal-price-input").value = price; // This should be the actual price
    document.getElementById("modal-img-input").value = img;
    
    // DEBUG: Verify the hidden field was set
    console.log("Hidden price field value:", document.getElementById("modal-price-input").value);
}

function closeModal() {
    document.getElementById("productModal").style.display = "none";
}
