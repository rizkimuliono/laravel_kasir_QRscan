@extends('app')
@section('title', 'Dashboard')
@section('content')
    <h1>Scan Barcode/QR Code</h1>
    <div class="row">
        <div class="col-md-6">
            <div id="reader" style="width: 100%;"></div>
        </div>
        <div class="col-md-6">
            <p>Product Code: <span style="font-weight: bold" id="code"></span></p>
            <p id="result" class="mt-3"></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            fetch('/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: decodedText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('result');
                    const resultCode = document.getElementById('code');
                    if (data.success) {
                        const product = data.product;
                        resultCode.innerHTML = `${data.code}`;
                        resultDiv.innerHTML = `
                    <p><strong>Nama:</strong> ${product.name}</p>
                    <p><strong>SKU:</strong> ${product.sku}</p>
                    <p><strong>Harga:</strong> Rp ${parseInt(product.price).toLocaleString()}</p>
                    <p><strong>Deskripsi:</strong> ${product.description}</p>
                    <img src="${product.image}" alt="Produk" style="max-width: 60%; height: auto;">
                `;
                    } else {
                        resultCode.innerHTML = `${data.code}`;
                        resultDiv.innerHTML = `<p class="text-danger">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    document.getElementById('result').innerHTML = `<p class="text-danger">Terjadi kesalahan.</p>`;
                    console.error('Error:', error);
                });
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
@endpush
