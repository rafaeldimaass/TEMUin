document.addEventListener('DOMContentLoaded', function() {
        // ===== Image Upload =====
        const imageUpload = document.getElementById('tim-img-upload');
        const imagePreview = document.getElementById('img-preview');
        const imagePath = document.getElementById('tim-image-path');

        imageUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran gambar maksimal 2MB');
                    return;
                }

                // Validasi tipe file
                if (!file.type.startsWith('image/')) {
                    alert('Silakan pilih file gambar');
                    imageUpload.value = '';
                    return;
                }

                // Preview gambar
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);

                // Simpan path untuk dikirim ke server
                if (imagePath) {
                    imagePath.value = file.name;
                }
            }
        });

        // ===== Skills Chip =====
        const skillsInput = document.getElementById('skillsInput');
        const chipList = document.getElementById('chipList');
        const chipContainer = document.getElementById('chip-container');

        let skillsArray = [];
        if (skillsInput && chipList) {
        skillsInput.addEventListener('keydown', function (e) {
            // Jika user menekan tombol Enter atau Koma (,)
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                let skillValue = skillsInput.value.trim().replace(',', '');

                if (skillValue !== '' && !skillsArray.includes(skillValue)) {
                    skillsArray.push(skillValue);
                    renderChips();
                    skillsInput.value = '';
                }
            }
        });

        // Fokus ke input ketika container diklik
        chipContainer.addEventListener('click', function () {
            skillsInput.focus();
        });
    }


    function renderChips() {
        if (!chipList) return;
        // Hapus elemen chip yang lama
        chipList.innerHTML = '';
        
        skillsArray.forEach((skill, index) => {
            const chip = document.createElement('div');
            chip.classList.add('chip');
            chip.innerHTML = `
                <span>${skill}</span>
                <button type="button" class="remove-chip" data-index="${index}">&times;</button>
            `;
            chipList.appendChild(chip);
        });

        // Buat hidden input atau update value pada input tersembunyi agar bisa dikirim via POST
        updateSkillsInput();
        
        // Tambahkan event listener untuk tombol hapus pada chip
        document.querySelectorAll('.remove-chip').forEach(button => {
            button.addEventListener('click', function () {
                const index = this.getAttribute('data-index');
                skillsArray.splice(index, 1);
                renderChips();
            });
        });

        function updateSkillsInput() {
        if (!skillsInput) return;
        // bisa memasukkan gabungan string (misal dipisahkan koma) ke input tersembunyi atau input utama
        let hiddenSkillsInput = document.getElementById('hidden-skills');
        if (!hiddenSkillsInput) {
            hiddenSkillsInput = document.createElement('input');
            hiddenSkillsInput.type = 'hidden';
            hiddenSkillsInput.id = 'hidden-skills';
            hiddenSkillsInput.name = 'skills';
            skillsInput.parentNode.appendChild(hiddenSkillsInput);
        }
        hiddenSkillsInput.value = skillsArray.join(', ');
        // Kosongkan atribut name pada input teks biasa agar tidak bentrok, 
        // biarkan hidden input yang mengirim data gabungannya ke backend.
        skillsInput.removeAttribute('name');
    }
    }  
        // ===== Set minimal date to today for deadline =====
        const tanggalInput = document.getElementById('tanggal-penutupan');
        const today = new Date().toISOString().split('T')[0];
        tanggalInput.setAttribute('min', today);
    });