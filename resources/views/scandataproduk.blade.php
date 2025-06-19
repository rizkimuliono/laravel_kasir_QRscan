@extends('app')
@section('title', 'Scan Produk')
@section('content')
    <div class="row">
        <div class="col-md-9">
            <input type="text" id="barcodeInput" class="form-control mb-3" placeholder="Barcode (hasil scan muncul disini)" autofocus>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th style="width: 20%;">Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="cartTableBody"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-end"><strong>Sum Total:</strong></td>
                        <td id="sumTotal">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md-3">
            <p><strong>Product Code:</strong> <span id="productCode"></span></p>
            <p><strong>Nama:</strong> <span id="productName"></span></p>
            <p><strong>SKU:</strong> <span id="productSku"></span></p>
            <p><strong>Harga:</strong> <span id="productPrice"></span></p>
            <p><strong>Deskripsi:</strong> <span id="productDescription"></span></p>
            <img id="productImage" src="" style="max-width: 60%; height: auto;" alt="">
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        function updateCartTable() {
            const tbody = document.getElementById('cartTableBody');
            tbody.innerHTML = '';
            let total = 0;

            cart.forEach((item, index) => {
                const subtotal = item.price * item.qty;
                total += subtotal;

                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${index + 1}</td>
                <td><img src="${item.image}" style="max-width: 70%; height: auto;" alt=""></td>
                <td>${item.name}</td>
                <td>${item.sku}</td>
                <td>Rp ${item.price.toLocaleString()}</td>
                <td>
                    <input type="number" min="1" value="${item.qty}" data-index="${index}" class="form-control qty-input">
                </td>
                <td>Rp ${(subtotal).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-danger delete-btn" data-index="${index}">Hapus</button>
                </td>
            `;
                tbody.appendChild(row);
            });

            document.getElementById('sumTotal').textContent = `Rp ${total.toLocaleString()}`;
            saveCart();
        }

        // Ubah qty manual
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input')) {
                const index = e.target.getAttribute('data-index');
                cart[index].qty = parseInt(e.target.value) || 1;
                updateCartTable();
            }
        });

        // Hapus item
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-btn')) {
                const index = e.target.getAttribute('data-index');
                cart.splice(index, 1);
                updateCartTable();
            }
        });

        const barcodeInput = document.getElementById('barcodeInput');
        let scanTimer;

        function processBarcode(code) {
            if (!code) return;

            fetch('/scan-produk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const p = data.product;
                        const existingIndex = cart.findIndex(item => item.sku === p.sku);
                        if (existingIndex !== -1) {
                            cart[existingIndex].qty += 1;
                        } else {

                            cart.push({
                                name: p.name,
                                image: p.image,
                                sku: p.sku,
                                price: p.price,
                                qty: 1
                            });
                        }

                        updateCartTable();

                        // Tampilkan detail
                        document.getElementById('productCode').textContent = data.code;
                        document.getElementById('productName').textContent = p.name;
                        document.getElementById('productSku').textContent = p.sku;
                        document.getElementById('productPrice').textContent = 'Rp ' + parseInt(p.price).toLocaleString();
                        document.getElementById('productDescription').textContent = p.description;
                        document.getElementById('productImage').src = p.image;

                        barcodeInput.value = '';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => console.error(err));
        }

        // Auto-trigger saat barcode scanner input
        barcodeInput.addEventListener('input', function() {
            clearTimeout(scanTimer);
            scanTimer = setTimeout(() => {
                const code = barcodeInput.value.trim();
                if (code !== '') {
                    processBarcode(code);
                }
            }, 300); // waktu jeda scan
        });

        // Saat halaman dimuat
        window.onload = function() {
            barcodeInput.focus();
            updateCartTable();
        }
    </script>
@endpush
