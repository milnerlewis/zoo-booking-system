class RzaApplication {
    constructor() {
        this.pagePath = window.location.pathname;
        this.init();
    }

    init() {
        this.setupLogoNavigation();
        this.setupAccessibility();
        this.setupRealtimeValidation();
        this.setupPageForms();
        this.setupBookingCalculator();
        this.setupContactCounter();
    }

    setupLogoNavigation() {
        const logo = document.querySelector('.logo');

        if (!logo) {
            return;
        }

        logo.setAttribute('tabindex', '0');
        logo.setAttribute('role', 'button');
        logo.setAttribute('aria-label', 'Go to home page');

        const goHome = () => {
            window.location.href = 'index.php';
        };

        logo.addEventListener('click', goHome);
        logo.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                goHome();
            }
        });
    }

    setupAccessibility() {
        document.addEventListener('focusin', (event) => {
            event.target.classList.add('focused');
        });

        document.addEventListener('focusout', (event) => {
            event.target.classList.remove('focused');
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            const target = event.target;
            if (target.classList.contains('btn') || target.classList.contains('action-btn')) {
                event.preventDefault();
                target.click();
            }
        });
    }

    setupPageForms() {
        const form = document.querySelector('form[method="POST"], form[method="post"]');
        if (!form) {
            return;
        }

        if (this.pagePath.endsWith('/login.php')) {
            form.addEventListener('submit', (event) => this.validateLoginForm(event));
            return;
        }

        if (this.pagePath.endsWith('/signup.php')) {
            form.addEventListener('submit', (event) => this.validateSignupForm(event));
        }
    }

    setupRealtimeValidation() {
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach((input) => {
            input.addEventListener('blur', () => this.validateEmail(input));
            input.addEventListener('input', () => this.clearValidationMessage(input));
        });

        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach((input) => {
            input.addEventListener('blur', () => this.validatePassword(input));
            input.addEventListener('input', () => this.clearValidationMessage(input));
        });

        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach((input) => {
            input.addEventListener('blur', () => this.validatePhone(input));
            input.addEventListener('input', () => this.formatPhone(input));
        });
    }

    validateEmail(input) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailPattern.test(input.value.trim());

        if (!isValid && input.value.length > 0) {
            this.showValidationMessage(input, 'Please enter a valid email address', 'error');
        } else if (isValid) {
            this.showValidationMessage(input, 'Valid email format', 'success');
        }

        return isValid;
    }

    validatePassword(input) {
        const password = input.value;
        const checks = {
            minLength: password.length >= 8,
            hasUpper: /[A-Z]/.test(password),
            hasLower: /[a-z]/.test(password),
            hasNumber: /\d/.test(password),
            hasSpecial: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        const score = Object.values(checks).filter(Boolean).length;

        if (password.length === 0) {
            this.clearValidationMessage(input);
            return false;
        }

        if (score <= 2) {
            this.showValidationMessage(input, 'Weak password - use 8+ chars, uppercase, lowercase, and numbers', 'error');
            return false;
        }

        if (score === 3) {
            this.showValidationMessage(input, 'Fair password - consider adding special characters', 'warning');
            return true;
        }

        if (score === 4) {
            this.showValidationMessage(input, 'Good password strength', 'success');
            return true;
        }

        this.showValidationMessage(input, 'Excellent password strength', 'success');
        return true;
    }

    validatePhone(input) {
        const phonePattern = /^[\+]?[0-9\s\-()]{10,15}$/;
        const isValid = phonePattern.test(input.value.trim());

        if (!isValid && input.value.length > 0) {
            this.showValidationMessage(input, 'Please enter a valid phone number', 'error');
        } else if (isValid) {
            this.showValidationMessage(input, 'Valid phone number', 'success');
        }

        return isValid;
    }

    formatPhone(input) {
        let value = input.value.replace(/\D/g, '');

        if (value.length <= 3) {
            input.value = value;
            return;
        }

        if (value.length <= 6) {
            input.value = `${value.slice(0, 3)} ${value.slice(3)}`;
            return;
        }

        input.value = `${value.slice(0, 3)} ${value.slice(3, 6)} ${value.slice(6, 11)}`;
    }

    setupBookingCalculator() {
        const adultsInput = document.getElementById('adults');
        const childrenInput = document.getElementById('children');
        const familyTicketsInput = document.getElementById('family_tickets');

        if (!adultsInput && !childrenInput && !familyTicketsInput) {
            return;
        }

        this.insertPriceCalculator();

        [adultsInput, childrenInput, familyTicketsInput].forEach((input) => {
            if (input) {
                input.addEventListener('input', () => this.calculateBookingPrice());
            }
        });

        const useLoyaltyDiscount = document.getElementById('use_loyalty_discount');
        if (useLoyaltyDiscount) {
            useLoyaltyDiscount.addEventListener('change', () => this.calculateBookingPrice());
        }

        const visitDateInput = document.getElementById('visit_date');
        if (visitDateInput) {
            visitDateInput.addEventListener('change', () => this.checkDateAvailability());
        }

        this.insertOptimizeButton();
        this.calculateBookingPrice();
    }

    insertPriceCalculator() {
        const form = document.querySelector('form[method="post"], form[method="POST"]');
        const submitButton = form?.querySelector('button[type="submit"]');

        if (!form || !submitButton || document.getElementById('price-calculator')) {
            return;
        }

        const calculatorMarkup = `
            <div id="price-calculator" class="price-calculator">
                <h4>Booking Summary</h4>
                <div class="price-breakdown">
                    <div class="price-line">
                        <span>Adults: <span id="adult-count">0</span> × £15.00</span>
                        <span id="adult-total">£0.00</span>
                    </div>
                    <div class="price-line">
                        <span>Children: <span id="children-count">0</span> × £12.00</span>
                        <span id="children-total">£0.00</span>
                    </div>
                    <div class="price-line">
                        <span>Family Tickets: <span id="family-count">0</span> × £45.00</span>
                        <span id="family-total">£0.00</span>
                    </div>
                    <div class="price-line subtotal">
                        <span>Subtotal:</span>
                        <span id="subtotal-cost">£0.00</span>
                    </div>
                    <div class="price-line discount" id="discount-line" style="display:none;">
                        <span>Loyalty Discount (10%):</span>
                        <span id="discount-amount">-£0.00</span>
                    </div>
                    <div class="price-line total">
                        <span><strong>Total Cost:</strong></span>
                        <span id="total-cost"><strong>£0.00</strong></span>
                    </div>
                    <div class="loyalty-info">
                        <small>Points earned: <span id="points-earned">0</span></small>
                    </div>
                </div>
            </div>
        `;

        submitButton.insertAdjacentHTML('beforebegin', calculatorMarkup);
    }

    insertOptimizeButton() {
        const calculator = document.getElementById('price-calculator');
        if (!calculator || document.getElementById('optimize-btn')) {
            return;
        }

        calculator.insertAdjacentHTML(
            'beforeend',
            '<button type="button" id="optimize-btn" class="optimize-btn">Optimize for Best Price</button>'
        );

        document.getElementById('optimize-btn')?.addEventListener('click', () => this.optimizeTicketSelection());
    }

    calculateBookingPrice() {
        const adults = Number.parseInt(document.getElementById('adults')?.value || '0', 10);
        const children = Number.parseInt(document.getElementById('children')?.value || '0', 10);
        const familyTickets = Number.parseInt(document.getElementById('family_tickets')?.value || '0', 10);
        const useDiscount = document.getElementById('use_loyalty_discount')?.checked || false;

        const adultTotal = adults * 15;
        const childrenTotal = children * 12;
        const familyTotal = familyTickets * 45;

        const subtotal = adultTotal + childrenTotal + familyTotal;
        const discountAmount = useDiscount ? subtotal * 0.1 : 0;
        const totalCost = subtotal - discountAmount;
        const pointsEarned = adults + children + (familyTickets * 5);

        this.updatePriceDisplay({
            adults,
            children,
            familyTickets,
            adultTotal,
            childrenTotal,
            familyTotal,
            subtotal,
            discountAmount,
            totalCost,
            pointsEarned,
            showDiscount: useDiscount
        });

        if (adults === 0 && children === 0 && familyTickets === 0) {
            this.showGlobalMessage('At least one visitor or family ticket is required for booking', 'error');
        } else {
            this.clearGlobalMessage();
        }
    }

    updatePriceDisplay(payload) {
        const fields = {
            'adult-count': payload.adults,
            'children-count': payload.children,
            'family-count': payload.familyTickets,
            'adult-total': `£${payload.adultTotal.toFixed(2)}`,
            'children-total': `£${payload.childrenTotal.toFixed(2)}`,
            'family-total': `£${payload.familyTotal.toFixed(2)}`,
            'subtotal-cost': `£${payload.subtotal.toFixed(2)}`,
            'discount-amount': `-£${payload.discountAmount.toFixed(2)}`,
            'points-earned': payload.pointsEarned
        };

        Object.entries(fields).forEach(([elementId, value]) => {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = String(value);
            }
        });

        const totalCostElement = document.getElementById('total-cost');
        if (totalCostElement) {
            totalCostElement.innerHTML = `<strong>£${payload.totalCost.toFixed(2)}</strong>`;
        }

        const discountLine = document.getElementById('discount-line');
        if (discountLine) {
            discountLine.style.display = payload.showDiscount ? 'flex' : 'none';
        }
    }

    optimizeTicketSelection() {
        const adultsInput = document.getElementById('adults');
        const childrenInput = document.getElementById('children');
        const familyTicketsInput = document.getElementById('family_tickets');

        if (!adultsInput || !childrenInput || !familyTicketsInput) {
            return;
        }

        const originalAdults = Number.parseInt(adultsInput.value || '0', 10);
        const originalChildren = Number.parseInt(childrenInput.value || '0', 10);

        if (originalAdults === 0 && originalChildren === 0) {
            this.showGlobalMessage('Please enter number of adults and children first', 'warning');
            return;
        }

        const maxFamilyTickets = Math.min(
            Math.floor(originalAdults / 2),
            Math.floor(originalChildren / 2)
        );

        const baseCost = (originalAdults * 15) + (originalChildren * 12);
        let bestOption = {
            adults: originalAdults,
            children: originalChildren,
            familyTickets: 0,
            cost: baseCost
        };

        for (let count = 1; count <= maxFamilyTickets; count += 1) {
            const remainingAdults = originalAdults - (count * 2);
            const remainingChildren = originalChildren - (count * 2);
            const optimizedCost = (count * 45) + (remainingAdults * 15) + (remainingChildren * 12);

            if (optimizedCost < bestOption.cost) {
                bestOption = {
                    adults: remainingAdults,
                    children: remainingChildren,
                    familyTickets: count,
                    cost: optimizedCost
                };
            }
        }

        adultsInput.value = String(bestOption.adults);
        childrenInput.value = String(bestOption.children);
        familyTicketsInput.value = String(bestOption.familyTickets);

        this.calculateBookingPrice();

        const savings = baseCost - bestOption.cost;
        if (savings > 0) {
            this.showGlobalMessage(`Optimized! You'll save £${savings.toFixed(2)} with family tickets`, 'success');
            return;
        }

        this.showGlobalMessage('Current selection is already optimal', 'info');
    }

    checkDateAvailability() {
        const visitDateInput = document.getElementById('visit_date');
        if (!visitDateInput || !visitDateInput.value) {
            return;
        }

        const selectedDate = new Date(visitDateInput.value);
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        let message = 'Weekday - Good availability';
        let type = 'success';

        if (selectedDate <= today) {
            message = 'Please select a future date';
            type = 'error';
        } else if (this.isHolidayPeriod(selectedDate)) {
            message = 'Holiday period - Very busy, book early!';
            type = 'warning';
        } else if (selectedDate.getDay() === 0 || selectedDate.getDay() === 6) {
            message = 'Weekend - Moderately busy';
            type = 'warning';
        }

        this.showValidationMessage(visitDateInput, message, type);
    }

    isHolidayPeriod(date) {
        const month = date.getMonth();
        const day = date.getDate();

        if (month === 6 || month === 7) {
            return true;
        }

        if (month === 11 && day >= 20) {
            return true;
        }

        return month === 2 || month === 3;
    }

    setupContactCounter() {
        const messageField = document.getElementById('message');
        if (!messageField) {
            return;
        }

        this.addCharacterCounter(messageField, 1000);
    }

    addCharacterCounter(textarea, maxLength) {
        if (document.getElementById('char-count')) {
            return;
        }

        const counterMarkup = `
            <div class="char-counter">
                <span id="char-count">${textarea.value.length}</span>/${maxLength} characters
            </div>
        `;

        textarea.insertAdjacentHTML('afterend', counterMarkup);

        textarea.addEventListener('input', () => {
            const counter = document.getElementById('char-count');
            if (!counter) {
                return;
            }

            const currentLength = textarea.value.length;
            counter.textContent = String(currentLength);
            counter.parentElement.style.color = currentLength > maxLength ? '#dc3545' : '#666';

            if (currentLength > maxLength) {
                this.showValidationMessage(textarea, `Message is too long (${currentLength}/${maxLength} characters)`, 'error');
            } else {
                this.clearValidationMessage(textarea);
            }
        });
    }

    validateLoginForm(event) {
        const usernameInput = event.target.querySelector('input[name="username"]');
        const passwordInput = event.target.querySelector('input[name="password"]');

        let valid = true;

        if (usernameInput && usernameInput.value.trim().length === 0) {
            this.showValidationMessage(usernameInput, 'Username is required', 'error');
            valid = false;
        }

        if (passwordInput && passwordInput.value.trim().length === 0) {
            this.showValidationMessage(passwordInput, 'Password is required', 'error');
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }
    }

    validateSignupForm(event) {
        const usernameInput = event.target.querySelector('input[name="username"]');
        const emailInput = event.target.querySelector('input[name="email"]');
        const passwordInput = event.target.querySelector('input[name="password"]');
        const confirmPasswordInput = event.target.querySelector('input[name="confirm_password"]');

        let valid = true;

        if (usernameInput && usernameInput.value.trim().length < 3) {
            this.showValidationMessage(usernameInput, 'Username must be at least 3 characters', 'error');
            valid = false;
        }

        if (emailInput && !this.validateEmail(emailInput)) {
            valid = false;
        }

        if (passwordInput && !this.validatePassword(passwordInput)) {
            valid = false;
        }

        if (
            passwordInput
            && confirmPasswordInput
            && passwordInput.value !== confirmPasswordInput.value
        ) {
            this.showValidationMessage(confirmPasswordInput, 'Passwords do not match', 'error');
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }
    }

    showValidationMessage(input, message, type = 'info') {
        if (!input) {
            return;
        }

        this.clearValidationMessage(input);

        const messageElement = document.createElement('div');
        messageElement.className = `validation-message validation-${type}`;
        messageElement.textContent = message;
        messageElement.id = `validation-${input.id || input.name || 'field'}`;

        input.parentNode.insertBefore(messageElement, input.nextSibling);
    }

    clearValidationMessage(input) {
        if (!input) {
            return;
        }

        const messageId = `validation-${input.id || input.name || 'field'}`;
        const existingMessage = document.getElementById(messageId);

        if (existingMessage) {
            existingMessage.remove();
        }
    }

    showGlobalMessage(message, type = 'info') {
        this.clearGlobalMessage();

        const messageElement = document.createElement('div');
        messageElement.className = `global-message global-${type}`;
        messageElement.textContent = message;
        messageElement.id = 'global-message';

        document.body.insertAdjacentElement('afterbegin', messageElement);

        window.setTimeout(() => {
            this.clearGlobalMessage();
        }, 5000);
    }

    clearGlobalMessage() {
        const existingMessage = document.getElementById('global-message');
        if (existingMessage) {
            existingMessage.remove();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new RzaApplication();
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = RzaApplication;
}
