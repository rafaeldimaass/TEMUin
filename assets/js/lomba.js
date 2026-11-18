document.addEventListener('DOMContentLoaded', function () {
    const posterInput = document.getElementById('poster-upload');
    const imgPreview = document.getElementById('img-preview');

    if (posterInput && imgPreview) {
        posterInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validasi tipe file gambar
            if (!file.type.startsWith('image/')) {
                alert('Harap pilih file gambar yang valid (JPG, PNG, WEBP).');
                posterInput.value = '';
                return;
            }

            // Validasi ukuran maksimal (misal 5MB)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran file poster maksimal 5MB.');
                posterInput.value = '';
                return;
            }

            // Render preview gambar poster
            const reader = new FileReader();
            reader.onload = function (event) {
                imgPreview.innerHTML = `
                    <img src="${event.target.result}" alt="Preview Poster" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                `;
                imgPreview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    }
});