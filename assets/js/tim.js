document.addEventListener('DOMContentLoaded', function() {
        // ===== Image Upload =====
        const imageUpload = document.getElementById('tim-img-upload');
        const imagePreview = document.getElementById('img-preview');
        const imagePath = document.getElementById('tim-img-path');

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
        const skillsHidden = document.getElementById('skillsHidden');
        const skillsInput = document.getElementById('skillsInput');
        const chipList = document.getElementById('chipList');
        const chipContainer = document.getElementById('chip-container');

        let skillsArray = [];

        function updateSkillsInput() {
        if (!skillsHidden) return;

        skillsHidden.value = skillsArray.join(', ');

        // Validasi agar minimal ada 1 skill sebelum submit
        if (skillsArray.length === 0) {
            skillsHidden.setCustomValidity('Tambahkan minimal satu skill terlebih dahulu.');
        } else {
            skillsHidden.setCustomValidity('');
        }
    }
        if (skillsInput && chipList) {
        updateSkillsInput();
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

        // hidden input atau update value pada input tersembunyi agar bisa dikirim POST
        updateSkillsInput();
        
        // event listener untuk tombol hapus pada chip
        document.querySelectorAll('.remove-chip').forEach(button => {
            button.addEventListener('click', function () {
                const index = this.getAttribute('data-index');
                skillsArray.splice(index, 1);
                renderChips();
            });
        });

    }  
        // ===== Set minimal date to today for deadline =====
        const tanggalInput = document.getElementById('tanggal-penutupan');
        const today = new Date().toISOString().split('T')[0];
        tanggalInput.setAttribute('min', today);
    });