// public/assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {

    // --- Sidebar Toggle for Mobile ---
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-show');
        });
    }

    // --- Logic for Inline Filter Forms ---
    const filterForm = document.getElementById('inline-filter-form');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach(selectElement => {
            selectElement.addEventListener('change', () => {
                filterForm.submit();
            });
        });
    }
    
    // --- NEW: Centralized Logic for Dependent Dropdowns ---
    function setupDependentDropdown(sourceId, targetId, urlTemplate) {
        const sourceSelect = document.getElementById(sourceId);
        const targetSelect = document.getElementById(targetId);

        if (!sourceSelect || !targetSelect) {
            return;
        }

        const initialOption = targetSelect.querySelector('option');

        sourceSelect.addEventListener('change', function() {
            const sourceValue = this.value;
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            targetSelect.disabled = true;

            if (!sourceValue) {
                targetSelect.innerHTML = '';
                targetSelect.appendChild(initialOption); // Restore the original placeholder
                return;
            }
            
            const fetchUrl = urlTemplate.replace('${id}', sourceValue);

            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    targetSelect.innerHTML = ''; // Clear loading message
                    targetSelect.appendChild(initialOption); // Add back the placeholder
                    data.forEach(item => {
                        // Handles both customers (name) and versions (version_number)
                        const text = item.name || item.version_number; 
                        targetSelect.innerHTML += `<option value="${item.id}">${text}</option>`;
                    });
                    targetSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching dependent data:', error);
                    targetSelect.innerHTML = '<option value="">-- Error Loading --</option>';
                });
        });
    }
    
    // Setup for Dealer -> Customer dropdown on the Orders page
    setupDependentDropdown('dealer_id', 'customer_id', `${BASE_URL}/orders/api/customers/\${id}`);
    
    // Setup for SKU -> Version dropdown (used on Orders and Tickets pages)
    setupDependentDropdown('sku_id', 'sku_version_id', `${BASE_URL}/orders/api/versions/\${id}`);

    // --- Logic for Tabbed Interfaces (like Billing page) ---
    const tabbedInterfaces = document.querySelectorAll('.nav-tabs');
    tabbedInterfaces.forEach(tabNav => {
        const tabLinks = tabNav.querySelectorAll('a.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Deactivate all tabs and panes within the same card
                const parentCard = this.closest('.card');
                parentCard.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                parentCard.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                
                // Activate the clicked tab and its corresponding pane
                this.classList.add('active');
                const targetPane = document.querySelector(this.getAttribute('href'));
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    });

    // --- Logic for Payment Method Cards ---
    const paymentForm = document.getElementById('payment-method-form');
    if (paymentForm) {
        const paymentCards = document.querySelectorAll('.payment-card');
        const formTitle = document.getElementById('payment-form-title');
        const paymentIdInput = document.getElementById('payment_id');
        const deleteBtn = document.getElementById('delete-payment-btn');

        const fields = ['payment_bank', 'account_no', 'branch', 'ifsc_code', 'upi_vpa'];

        const resetForm = () => {
            formTitle.textContent = 'Add New Payment Method';
            paymentIdInput.value = '';
            fields.forEach(field => document.getElementById(field).value = '');
            document.getElementById('is_preferred').checked = false;
            deleteBtn.style.display = 'none';
        };

        paymentCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove active state from all cards
                paymentCards.forEach(c => c.classList.remove('active'));
                
                if (this.id === 'add-new-payment-card') {
                    resetForm();
                    this.classList.add('active');
                    return;
                }
                
                // Set active state on clicked card
                this.classList.add('active');

                // Populate form
                formTitle.textContent = 'Edit Payment Method';
                const data = this.dataset;
                paymentIdInput.value = data.id;

                fields.forEach(field => {
                    const camelCaseKey = field.replace(/_([a-z])/g, g => g[1].toUpperCase());
                    document.getElementById(field).value = data[camelCaseKey] || '';
                });
                
                document.getElementById('is_preferred').checked = (data.preferred === '1');
                
                // Show and configure delete button
                deleteBtn.style.display = 'inline-block';
                deleteBtn.onclick = function() {
                    if (confirm('Are you sure you want to delete this payment method?')) {
                        const deleteForm = document.createElement('form');
                        deleteForm.method = 'POST';
                        deleteForm.action = `billing/delete_payment/${data.id}`;
                        document.body.appendChild(deleteForm);
                        deleteForm.submit();
                    }
                }
            });
        });

        // Set "Add New" as active by default
        const addNewCard = document.getElementById('add-new-payment-card');
        if(addNewCard){
            addNewCard.classList.add('active');
        }
    }

    // --- Centralized logic for Order and Ticket Forms ---
    const orderForm = document.querySelector('form[action*="/orders/store"]');
    const ticketForm = document.querySelector('form[action*="/tickets/store"]');

    if (orderForm || ticketForm) {
        const dealerSelect = document.getElementById('dealer_id');
        const customerSelect = document.getElementById('customer_id');
        const skuSelect = document.getElementById('sku_id');
        const versionSelectBtn = document.getElementById('select_version_btn');
        const versionDisplay = document.getElementById('sku_version_display');
        const versionInput = document.getElementById('sku_version_id');
        const uomContainer = document.getElementById('uom_options');
        const apiBaseUrl = document.body.dataset.apiUrl; // Get base URL from body data attribute

        // Logic for fetching customers based on dealer (for admins on order form)
        if (dealerSelect) {
            dealerSelect.addEventListener('change', function() {
                const dealerId = this.value;
                customerSelect.innerHTML = '<option value="">Loading...</option>';
                customerSelect.disabled = true;

                if (!dealerId) {
                    customerSelect.innerHTML = '<option value="">-- Select a Dealer First --</option>';
                    return;
                }

                fetch(`${apiBaseUrl}?action=get_customers&dealer_id=${dealerId}`)
                    .then(response => response.json())
                    .then(data => {
                        customerSelect.innerHTML = '<option value="">-- Select a Customer --</option>';
                        data.forEach(customer => {
                            customerSelect.innerHTML += `<option value="${customer.id}">${customer.name}</option>`;
                        });
                        customerSelect.disabled = false;
                        
                        const urlParams = new URLSearchParams(window.location.search);
                        const preselectedCustomerId = urlParams.get('customer_id');
                        if (preselectedCustomerId) {
                            customerSelect.value = preselectedCustomerId;
                        }
                    });
            });
             // Pre-select if dealer_id is in URL
            const urlParams = new URLSearchParams(window.location.search);
            const preselectedDealerId = urlParams.get('dealer_id');
            if (preselectedDealerId) {
                dealerSelect.value = preselectedDealerId;
                dealerSelect.dispatchEvent(new Event('change'));
            }
        } else {
             // For dealers, pre-select customer if in URL
            const urlParams = new URLSearchParams(window.location.search);
            const preselectedCustomerId = urlParams.get('customer_id');
            if (preselectedCustomerId) {
                customerSelect.value = preselectedCustomerId;
            }
        }
        

        // Logic for SKU selection change
        skuSelect.addEventListener('change', function() {
            // Reset version when SKU changes
            if (versionDisplay) versionDisplay.value = '';
            if (versionInput) versionInput.value = '';

            // Update UoM options on the order form
            if (uomContainer) {
                const selectedOption = this.options[this.selectedIndex];
                const isYearly = selectedOption.dataset.isYearly === '1';
                const isPerpetual = selectedOption.dataset.isPerpetual === '1';
                uomContainer.innerHTML = '';
                if (isYearly) {
                    uomContainer.innerHTML += `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="uom" id="uom_yearly" value="yearly" checked>
                            <label class="form-check-label" for="uom_yearly">Yearly</label>
                        </div>`;
                }
                if (isPerpetual) {
                    uomContainer.innerHTML += `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="uom" id="uom_perpetual" value="perpetual" ${!isYearly ? 'checked' : ''}>
                            <label class="form-check-label" for="uom_perpetual">Perpetual</label>
                        </div>`;
                }
                if (!isYearly && !isPerpetual) {
                    uomContainer.innerHTML = '<p class="text-muted">This SKU has no pricing options defined.</p>';
                }
            }
        });

        // --- SKU Version Popup Logic ---
        if (versionSelectBtn) {
            versionSelectBtn.addEventListener('click', function() {
                const skuId = skuSelect.value;
                if (!skuId) {
                    alert('Please select an SKU first.');
                    return;
                }
                // --- FIX IS HERE: API URL now matches the one in routes.php ---
                const apiUrl = `${BASE_URL}/orders/api/versions/${skuId}`;

                fetch(apiUrl)
                    .then(response => response.json())
                    .then(versions => {
                        const modal = document.getElementById('sku-version-modal');
                        const list = document.getElementById('sku-version-list');
                        list.innerHTML = ''; // Clear previous versions

                        if (versions.length > 0) {
                            versions.forEach(version => {
                                const row = document.createElement('tr');
                                // --- FIX IS HERE: Populate table with more details ---
                                row.innerHTML = `
                                    <td data-label="Version">${version.version_number}</td>
                                    <td data-label="Release Date">${version.release_date}</td>
                                    <td data-label="Description">${version.description || ''}</td>
                                    <td data-label="Tally Compatibility">${version.tally_compat_from || ''}</td>
                                    <td data-label="Select">
                                        <button type="button" class="btn btn-sm btn-primary select-version-btn" 
                                                data-id="${version.id}" 
                                                data-number="${version.version_number}">Select</button>
                                    </td>
                                `;
                                list.appendChild(row);
                            });

                            // Add event listeners to the new "Select" buttons inside the modal
                            list.querySelectorAll('.select-version-btn').forEach(button => {
                                button.addEventListener('click', function() {
                                    versionDisplay.value = this.dataset.number;
                                    versionInput.value = this.dataset.id;
                                    modal.style.display = 'none';
                                });
                            });
                            // --- END OF FIX ---

                        } else {
                            list.innerHTML = '<tr><td colspan="5">No versions found for this SKU.</td></tr>';
                        }

                        modal.style.display = 'block';
                    });
            });
        }
        
        // Close modal logic
        const closeBtn = document.querySelector('.modal .close');
        if (closeBtn) {
            closeBtn.onclick = function() {
                document.getElementById('sku-version-modal').style.display = 'none';
            }
        }
        window.onclick = function(event) {
            const modal = document.getElementById('sku-version-modal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
    
    // --- Logic for conditional requirement on Ticket Form ---
    if (ticketForm) {
        const ticketTypeSelect = document.getElementById('type');
        const versionInput = document.getElementById('sku_version_id');

        ticketTypeSelect.addEventListener('change', function() {
            if (this.value === 'feature_request') {
                versionInput.required = false;
            } else {
                versionInput.required = true;
            }
        });
        // Trigger on page load as well
        ticketTypeSelect.dispatchEvent(new Event('change'));
    }

        // --- NEW: Logic for Trial Request Form ---
    function setupTrialRequestForm() {
        const orderSelect = document.getElementById('order_id');
        if (!orderSelect) return;

        const customerNameDisplay = document.getElementById('customer_name_display');
        const serialSelect = document.getElementById('customer_serial_id');
        const apiBaseUrl = `${BASE_URL}/orders/api`;

        orderSelect.addEventListener('change', function() {
            const orderId = this.value;

            // Reset dependent fields
            customerNameDisplay.value = 'Loading...';
            serialSelect.innerHTML = '<option value="">Loading...</option>';
            serialSelect.disabled = true;

            if (!orderId) {
                customerNameDisplay.value = '-- Select an order --';
                serialSelect.innerHTML = '<option value="">-- Select an order --</option>';
                return;
            }

            fetch(`${apiBaseUrl}/details/${orderId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch order details.');
                    return response.json();
                })
                .then(data => {
                    customerNameDisplay.value = data.customer_name || 'N/A';
                    
                    serialSelect.innerHTML = '<option value="">-- Select a Serial --</option>';
                    if (data.serials && data.serials.length > 0) {
                        data.serials.forEach(serial => {
                            serialSelect.innerHTML += `<option value="${serial.id}">${serial.serial_number}</option>`;
                        });
                        serialSelect.disabled = false;
                    } else {
                        serialSelect.innerHTML = '<option value="">-- No serials found for this customer --</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    customerNameDisplay.value = 'Error loading customer';
                    serialSelect.innerHTML = '<option value="">-- Error --</option>';
                });
        });
    }

    // Initialize the new functionality
    setupTrialRequestForm();

});