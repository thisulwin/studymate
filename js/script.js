document.addEventListener('DOMContentLoaded', function() {

    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    var profileToggle = document.getElementById('profileToggle');
    if (profileToggle) {
        profileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var wrap = this.closest('.profile-menu-wrap');
            wrap.classList.toggle('open');
        });
    }

    document.addEventListener('click', function(e) {
        var profileWrap = document.querySelector('.profile-menu-wrap');
        if (profileWrap && !profileWrap.contains(e.target)) {
            profileWrap.classList.remove('open');
        }
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const fadeElements = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    fadeElements.forEach(el => observer.observe(el));

    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            this.querySelectorAll('[required]').forEach(input => {
                const errorEl = input.parentNode.querySelector('.error-text');
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('error');
                    if (errorEl) {
                        errorEl.style.display = 'block';
                        errorEl.textContent = 'This field is required';
                    }
                } else {
                    input.classList.remove('error');
                    if (errorEl) errorEl.style.display = 'none';
                }
            });

            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput && emailInput.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    valid = false;
                    emailInput.classList.add('error');
                    const errorEl = emailInput.parentNode.querySelector('.error-text');
                    if (errorEl) {
                        errorEl.style.display = 'block';
                        errorEl.textContent = 'Please enter a valid email';
                    }
                }
            }

            const passInput = this.querySelector('input[name="password"]');
            const confirmInput = this.querySelector('input[name="confirm_password"]');
            if (passInput && confirmInput && passInput.value !== confirmInput.value) {
                valid = false;
                confirmInput.classList.add('error');
                const errorEl = confirmInput.parentNode.querySelector('.error-text');
                if (errorEl) {
                    errorEl.style.display = 'block';
                    errorEl.textContent = 'Passwords do not match';
                }
            }

            if (!valid) e.preventDefault();
        });
    });

    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            const errorEl = this.parentNode.querySelector('.error-text');
            if (errorEl) errorEl.style.display = 'none';
        });
    });

    document.querySelectorAll('[data-auth]').forEach(el => {
        el.addEventListener('click', function(e) {
            const isLoggedIn = this.dataset.auth === 'true';
            if (!isLoggedIn) {
                e.preventDefault();
                showToast('Please login to use this feature', 'error');
                setTimeout(() => {
                    window.location.href = '/t-project/auth/login.php';
                }, 1500);
            }
        });
    });

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const group = this.closest('.tabs');
            const target = this.dataset.tab;
            group.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll(`.tab-content[data-tab-content]`).forEach(c => {
                c.classList.remove('active');
            });
            const targetContent = document.querySelector(`.tab-content[data-tab-content="${target}"]`);
            if (targetContent) targetContent.classList.add('active');
        });
    });

    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modal = document.getElementById(this.dataset.modal);
            if (modal) modal.classList.add('active');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal-overlay').classList.remove('active');
        });
    });

    const searchInput = document.querySelector('#liveSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const items = document.querySelectorAll('.filterable-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    const filterSelect = document.querySelector('#categoryFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const value = this.value;
            const items = document.querySelectorAll('.filterable-item');
            items.forEach(item => {
                if (!value || item.dataset.category === value) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});

function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(30px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function toggleReply(commentId) {
    const form = document.getElementById('reply-' + commentId);
    if (form) form.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var tagWrapper = document.getElementById('tagInputWrapper');
    var tagInput = document.getElementById('tagInput');
    var tagChips = document.getElementById('tagChips');
    var tagsHidden = document.getElementById('tagsHidden');

    if (!tagInput || !tagChips || !tagsHidden) return;

    var tags = [];

    function renderTags() {
        tagChips.innerHTML = '';
        tags.forEach(function(tag, i) {
            var chip = document.createElement('span');
            chip.className = 'tag-chip';
            chip.innerHTML = tag + ' <button type="button" class="tag-remove" data-index="' + i + '">&times;</button>';
            tagChips.appendChild(chip);
        });
        tagsHidden.value = tags.join(', ');

        tagChips.querySelectorAll('.tag-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                tags.splice(parseInt(this.dataset.index), 1);
                renderTags();
            });
        });
    }

    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            var val = this.value.trim().replace(/,/g, '').toLowerCase();
            if (val.length >= 2 && val.length <= 50 && tags.indexOf(val) === -1) {
                tags.push(val);
                renderTags();
            }
            this.value = '';
        }
        if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
            tags.pop();
            renderTags();
        }
    });

    tagWrapper.addEventListener('click', function() {
        tagInput.focus();
    });
});
