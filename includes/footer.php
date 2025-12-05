<footer>
    <p>© 2025 189 Beauty — Luxury Cosmetics & Skincare</p>
</footer>

<script src="js/main.js"></script>
<div id="productModal" class="modal-overlay">
    <div class="modal-box">
        <span class="close-modal" onclick="closeModal()">X</span>

        <img id="modalImg" src="">
        <div class="modal-content">
            <h3 id="modalName"></h3>
            <p id="modalPrice" style="color:#d4af37; font-weight:bold;"></p>
            <p id="modalDesc"></p>

            <form method="POST" action="products.php">
                <input type="hidden" id="modalNameField" name="name">
                <input type="hidden" id="modalPriceField" name="price">
                <input type="hidden" id="modalImgField" name="img">

                <button class="btn" style="margin-top:15px;">Add to Cart</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
