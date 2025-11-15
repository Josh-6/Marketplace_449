// Section 1: Edit mode, will later need to add the ability to
// change profile pic and save it
// Select relevant elements
const editProfileBtn = document.querySelector('.edit-profile');
const cancelBtn = document.querySelector('.cancel-btn');
const profileHeader = document.querySelector('.profile-header');

// Function to enter edit mode
function enterEditMode() {
    profileHeader.classList.add('edit-mode');
}

// Function to exit edit mode
function exitEditMode() {
    profileHeader.classList.remove('edit-mode');
}

// Event listeners
editProfileBtn.addEventListener('click', enterEditMode);
cancelBtn.addEventListener('click', exitEditMode);

// Section 2 & 3: Tab Switching Logic
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const target = tab.dataset.tab; // item-history, order-history, or account-settings

            // Remove 'active' from all tabs and tab contents
            tabs.forEach(t => t.classList.remove("active"));
            tabContents.forEach(tc => tc.classList.remove("active"));

            tab.classList.add("active");

            // Show tab content
            const activeContent = document.getElementById(target);
            if (activeContent) {
                activeContent.classList.add("active");
            }

            //console.log(`Switched to tab: ${target}`);
        });
    });
});

/* ---------------- Account Settings nested navigation ---------------- */
    const accountNavButtons = document.querySelectorAll('.account-nav .account');
    const accountPanels = document.querySelectorAll('.account-panel');

    function switchAccountPanel(panelId) {
        accountNavButtons.forEach(b => b.classList.toggle('active', b.dataset.content === panelId));
        accountPanels.forEach(p => p.classList.toggle('active', p.id === panelId));
    }

    accountNavButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetPanel = btn.dataset.content;
            switchAccountPanel(targetPanel);
        });
    });

    // default active panel is already set personal-information
    // ensure panels reflect that on page load
    const defaultBtn = document.querySelector('.account-nav .account.active');
    if (defaultBtn) switchAccountPanel(defaultBtn.dataset.content);

    /* ---------------- Edit / Save / Cancel logic for account info ---------------- */
    // Generic helpers
    function showEditForm(name) {
        //console.log(`Opening form: ${name}`);
        const form = document.querySelector(`.edit-form[data-form="${name}"]`);
        const display = document.querySelector(`.info-block[data-block="${name}"] .info-value`);
        if (form && display) {
            form.style.display = 'block';
            display.style.display = 'none';
        }
    }
    function hideEditForm(name) {
        //console.log(`Closing form: ${name}`);
        const form = document.querySelector(`.edit-form[data-form="${name}"]`);
        const display = document.querySelector(`.info-block[data-block="${name}"] .info-value`);
        if (form && display) {
            form.style.display = 'none';
            display.style.display = 'flex';
        }
    }

    // Wire edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = btn.dataset.edit; // e.g., username, phone, personal
            showEditForm(target);
            //console.log(`Clicked Edit: ${target}`);
        });
    });

    // Wire cancel buttons (small)
    document.querySelectorAll('.cancel-btn.small').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = btn.dataset.cancel;
            // reset inputs to previous values (from window.profileSession)
            if (target === 'username') {
                const input = document.querySelector('.input-username');
                if (input) input.value = window.profileSession.username || '';
            } else if (target === 'contact') {
                const phone = document.querySelector('.input-phone');
                const email = document.querySelector('.input-email');
                if (phone) phone.value = window.profileSession.phone || '';
                if (email) email.value = window.profileSession.email || '';
            } else if (target === 'personal') {
                const fn = document.querySelector('.input-fullname');
                const addr = document.querySelector('.input-address');
                if (fn) fn.value = window.profileSession.fullName || '';
                if (addr) addr.value = window.profileSession.address || '';
            }
            hideEditForm(target);
        });
    });

    // Wire save buttons (small)
    document.querySelectorAll('.save-btn.small').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const target = btn.dataset.save;

            if (target === 'username') {
                const input = document.querySelector('.input-username');
                if (!input) return;
                const newValue = input.value.trim() || 'No Username';
                // TODO: validate uniqueness on server later
                // For now, update DOM and log the change
                document.querySelector('.username-value').textContent = newValue;
                window.profileSession.username = newValue;
                hideEditForm('username');
                //console.log('USERNAME SAVE (client-side only):', newValue);
                // Future: POST to server endpoint to update
            }

            if (target === 'contact') {
                const phone = document.querySelector('.input-phone');
                const email = document.querySelector('.input-email');
                if (!phone || !email) return;
                //console.log(`Contact inputs good`);
                const newPhone = (phone && phone.value.trim()) || 'No Phone Number';
                const newEmail = (email && email.value.trim()) || 'No Email';
                const phoneElem = document.querySelector('.contact-info-value .phone-value');
                const emailElem = document.querySelector('.contact-info-value .email-value');

                if(phoneElem) phoneElem.innerHTML = newPhone ? escapeHtml(newPhone) : '<span class="no-info">No Phone Number</span>';
                if(emailElem) emailElem.innerHTML = newEmail ? escapeHtml(newEmail) : '<span class="no-info">No Email</span>';

                window.profileSession.phone = newPhone;
                window.profileSession.emial = newEmail;
                hideEditForm('contact');
                //console.log('CONTACT SAVE (client-side only):', {phone: newPhone, email: newEmail});
            }

            if (target === 'personal') {
                const fn = document.querySelector('.input-fullname');
                const addr = document.querySelector('.input-address');
                if (!fn || !addr) return;
                const newFn = (fn && fn.value.trim()) || '';
                const newAddr = (addr && addr.value.trim()) || '';
                const nameElem = document.querySelector('.personal-info-value .full-name');
                const addrElem = document.querySelector('.personal-info-value .address');

                if (nameElem) nameElem.innerHTML = newFn ? escapeHtml(newFn) : '<span class="no-info">No Information</span>';
                if (addrElem) addrElem.innerHTML = newAddr ? escapeHtml(newAddr).replace(/\n/g,'<br>') : '<span class="no-info">No Information</span>';

                window.profileSession.fullName = newFn;
                window.profileSession.address = newAddr;
                hideEditForm('personal');
                //console.log('PERSONAL SAVE (client-side only):', { fullName: newFn, address: newAddr });
            }

            // NOTE: Saves are client-side only. Will implement POST requests here to persist to Database.
        });
    });

    /* ---------------- Small helpers ---------------- */
    function escapeHtml(text) {
        return text.replace(/[&<>"'`=\/]/g, function (s) {
            return ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
            })[s];
        });
    }

    /* ---------------- Add payment / become seller button (placeholders) ---------------- */
    const addPaymentBtn = document.getElementById('add-payment-btn');
    if (addPaymentBtn) addPaymentBtn.addEventListener('click', () => {
        alert('Add Payment Method — functionality will be implemented in a later step.');
    });

    const becomeSellerBtn = document.getElementById('become-seller-btn');
    if (becomeSellerBtn) becomeSellerBtn.addEventListener('click', () => {
        alert('Become a Seller — server-side flow will be added later.');
    });

