document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('triggerUpload').addEventListener('click', function() {
    document.getElementById('hiddenFotoInput').click();
    });
    const triggerUpload = document.getElementById('triggerUpload');
    const hiddenFotoInput = document.getElementById('hiddenFotoInput');
    const previewAvatar = document.getElementById('previewAvatar');
    const defaultAvatarContainer = document.getElementById('defaultAvatarContainer');
    const avatarInitial = document.getElementById('avatarInitial');

    if (triggerUpload && hiddenFotoInput) {
        // 1. Saat tombol pensil diklik, buka dialog pemilihan file
        triggerUpload.addEventListener('click', function() {
            hiddenFotoInput.click();
        });
        // 2. Saat user memilih foto dari penyimpanan internal
        hiddenFotoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (previewAvatar) {
                        previewAvatar.src = e.target.result;
                        previewAvatar.classList.remove('d-none');
                    }
                    
                    if (defaultAvatarContainer) {
                        defaultAvatarContainer.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(file);
            }
        });
    }
});

      


