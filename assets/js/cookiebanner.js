/**
 * Havax Cookie Banner - JavaScript Module
 * @version 1.0.0
 * @author Havax
 * @license MIT
 */

(function(window, document) {
    'use strict';

    // Default configuration
    const DEFAULT_CONFIG = {
        cookieName: 'havax_cb_consent',
        cookieExpiry: 365,
        cookiePath: '/',
        cookieDomain: '',
        cookieSecure: true,
        cookieSameSite: 'Lax',
        autoBlock: true,
        blockingMode: false,
        categories: {},
        blockerPatterns: [],
        apiUrl: null, // URL for PHP API endpoint (e.g., '?api=1' or '/api/consent')
        onConsentGiven: null,
        onConsentUpdated: null,
        onConsentWithdrawn: null,
        onScriptLoaded: null,
        onScriptBlocked: null,
    };

    // Event names
    const EVENTS = {
        INIT: 'havax-cb:init',
        CONSENT_GIVEN: 'havax-cb:consent:given',
        CONSENT_UPDATED: 'havax-cb:consent:updated',
        CONSENT_WITHDRAWN: 'havax-cb:consent:withdrawn',
        BANNER_SHOWN: 'havax-cb:banner:shown',
        BANNER_HIDDEN: 'havax-cb:banner:hidden',
        PREFERENCES_OPENED: 'havax-cb:preferences:opened',
        PREFERENCES_CLOSED: 'havax-cb:preferences:closed',
        SCRIPT_LOADED: 'havax-cb:script:loaded',
        SCRIPT_BLOCKED: 'havax-cb:script:blocked',
        CATEGORY_ENABLED: 'havax-cb:category:enabled',
        CATEGORY_DISABLED: 'havax-cb:category:disabled',
    };

    class HavaxCbBanner {
        constructor(config = {}) {
            this.config = { ...DEFAULT_CONFIG, ...config };
            this.consent = null;
            this.banner = null;
            this.preferencesModal = null;
            this.floatingPanel = null;
            this.isInitialized = false;
            this.blockedScripts = [];
            this.loadedScripts = [];
            this.observers = [];

            this.init();
        }

        /**
         * Initialize the cookie banner
         */
        init() {
            if (this.isInitialized) return;

            this.loadConsent();
            this.setupElements();
            this.bindEvents();
            this.setupScriptBlocker();

            // Handle blocking mode
            if (this.config.blockingMode) {
                this.setupBlockingMode();
            }

            this.isInitialized = true;
            this.dispatchEvent(EVENTS.INIT, { consent: this.consent });

            // Auto-load scripts if consent exists
            if (this.hasConsent()) {
                this.activateConsentedScripts();
            }
        }

        /**
         * Setup blocking mode - prevents site usage without consent
         */
        setupBlockingMode() {
            if (!this.hasConsent()) {
                // Lock body scroll
                document.body.classList.add('havax-cb-blocking-active');

                // Show banner immediately
                this.showBanner();

                // Prevent any interaction with the page
                this.preventPageInteraction();
            }
        }

        /**
         * Prevent page interaction in blocking mode
         */
        preventPageInteraction() {
            // Disable all links and buttons outside the banner
            const disableInteraction = (e) => {
                const target = e.target;
                const banner = document.getElementById('havax-cb-cookie-banner');
                const prefsModal = document.getElementById('havax-cb-preferences-modal');

                // Allow interaction within banner and preferences modal
                if (banner && banner.contains(target)) return;
                if (prefsModal && prefsModal.contains(target)) return;

                // Allow policy links (they open in new tab)
                if (target.closest('.havax-cb-policy-link')) return;

                // Block everything else in blocking mode
                if (this.config.blockingMode && !this.hasConsent()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            };

            // Capture phase to intercept before handlers
            document.addEventListener('click', disableInteraction, true);
            document.addEventListener('keydown', (e) => {
                if (this.config.blockingMode && !this.hasConsent()) {
                    // Allow Tab and Escape for accessibility
                    if (e.key !== 'Tab' && e.key !== 'Escape') {
                        const banner = document.getElementById('havax-cb-cookie-banner');
                        if (!banner || !banner.contains(e.target)) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }
                }
            }, true);
        }

        /**
         * Load existing consent from cookie
         */
        loadConsent() {
            const cookieValue = this.getCookie(this.config.cookieName);
            if (cookieValue) {
                try {
                    this.consent = JSON.parse(atob(cookieValue));
                } catch (e) {
                    this.consent = null;
                }
            }
        }

        /**
         * Setup DOM elements
         */
        setupElements() {
            this.banner = document.getElementById('havax-cb-cookie-banner');
            this.preferencesModal = document.getElementById('havax-cb-preferences-modal');
            this.floatingPanel = document.getElementById('havax-cb-floating-panel');
            this.floatingButton = document.getElementById('havax-cb-floating-button');
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Delegate all button clicks
            document.addEventListener('click', (e) => {
                const action = e.target.closest('[data-action]');
                if (!action) return;

                const actionName = action.dataset.action;
                this.handleAction(actionName, e);
            });

            // Handle checkbox changes
            document.addEventListener('change', (e) => {
                if (e.target.matches('[data-category]')) {
                    this.handleCategoryChange(e.target);
                }
            });

            // Handle keyboard events
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closePreferences();
                }
            });
        }

        /**
         * Handle button actions
         */
        handleAction(action, event) {
            switch (action) {
                case 'accept-all':
                    this.acceptAll();
                    break;
                case 'reject-all':
                    this.rejectAll();
                    break;
                case 'save-preferences':
                    this.savePreferences();
                    break;
                case 'show-preferences':
                    this.showPreferences();
                    break;
                case 'close-preferences':
                    this.closePreferences();
                    break;
                case 'close-banner':
                    this.hideBanner();
                    break;
                case 'toggle-panel':
                    this.toggleFloatingPanel();
                    break;
            }
        }

        /**
         * Handle category checkbox change
         */
        handleCategoryChange(checkbox) {
            const category = checkbox.dataset.category;
            const isChecked = checkbox.checked;

            // Sync all checkboxes for the same category
            document.querySelectorAll(`[data-category="${category}"]`).forEach(cb => {
                if (cb !== checkbox) {
                    cb.checked = isChecked;
                }
            });
        }

        /**
         * Accept all cookies
         */
        acceptAll() {
            const categories = Object.keys(this.config.categories);
            this.giveConsent(categories, 'accept_all');
        }

        /**
         * Reject all optional cookies
         */
        rejectAll() {
            const requiredCategories = Object.entries(this.config.categories)
                .filter(([key, cat]) => cat.required)
                .map(([key]) => key);

            this.giveConsent(requiredCategories, 'reject_all');
        }

        /**
         * Save preferences from checkboxes
         */
        savePreferences() {
            const accepted = [];
            const checkboxes = document.querySelectorAll('[data-category]:checked');

            checkboxes.forEach(checkbox => {
                const category = checkbox.dataset.category;
                if (!accepted.includes(category)) {
                    accepted.push(category);
                }
            });

            // Always include required categories
            Object.entries(this.config.categories).forEach(([key, cat]) => {
                if (cat.required && !accepted.includes(key)) {
                    accepted.push(key);
                }
            });

            this.giveConsent(accepted, 'preferences');
        }

        /**
         * Give consent
         */
        giveConsent(acceptedCategories, method = 'banner') {
            const allCategories = Object.keys(this.config.categories);
            const rejectedCategories = allCategories.filter(cat => !acceptedCategories.includes(cat));
            const previousConsent = this.consent;
            const isFirstConsent = !previousConsent;

            // Generate consent data
            this.consent = {
                consent_id: this.generateConsentId(),
                accepted_categories: acceptedCategories,
                rejected_categories: rejectedCategories,
                timestamp: new Date().toISOString(),
                consent_method: method,
                user_agent: navigator.userAgent,
                page_url: window.location.href,
                referrer: document.referrer,
                previous_consent: previousConsent,
            };

            // Save to cookie
            this.saveConsentCookie();

            // Send to PHP API for server-side event handling
            this.sendToApi('give_consent', {
                categories: acceptedCategories,
                method: method,
                previous_consent: previousConsent, // Send JS previous consent to PHP
                metadata: {
                    user_agent: navigator.userAgent,
                    page_url: window.location.href,
                    referrer: document.referrer,
                    is_update: !isFirstConsent,
                },
            });

            // Dispatch events
            const eventType = isFirstConsent ? EVENTS.CONSENT_GIVEN : EVENTS.CONSENT_UPDATED;
            this.dispatchEvent(eventType, {
                consent: this.consent,
                acceptedCategories,
                rejectedCategories,
                method,
                isFirstConsent,
            });

            // Dispatch category-specific events
            acceptedCategories.forEach(category => {
                this.dispatchEvent(EVENTS.CATEGORY_ENABLED, { category, consent: this.consent });
            });

            rejectedCategories.forEach(category => {
                this.dispatchEvent(EVENTS.CATEGORY_DISABLED, { category, consent: this.consent });
            });

            // Activate consented scripts
            this.activateConsentedScripts();

            // Hide banner
            this.hideBanner();
            this.closePreferences();

            // Call callbacks
            if (isFirstConsent && typeof this.config.onConsentGiven === 'function') {
                this.config.onConsentGiven(this.consent);
            } else if (!isFirstConsent && typeof this.config.onConsentUpdated === 'function') {
                this.config.onConsentUpdated(this.consent);
            }
        }

        /**
         * Withdraw consent
         */
        withdrawConsent() {
            const previousConsent = this.consent;

            // Send to PHP API BEFORE deleting cookie so PHP can access consent data
            this.sendToApi('withdraw_consent', {
                previous_consent: previousConsent,
                metadata: {
                    previous_consent_id: previousConsent?.consent_id,
                },
            });

            // Now clear local state and cookie
            this.consent = null;
            this.deleteCookie(this.config.cookieName);

            // Dispatch event
            this.dispatchEvent(EVENTS.CONSENT_WITHDRAWN, {
                previousConsent,
            });

            // Call callback
            if (typeof this.config.onConsentWithdrawn === 'function') {
                this.config.onConsentWithdrawn(previousConsent);
            }

            // In blocking mode, re-lock the body
            if (this.config.blockingMode) {
                document.body.classList.add('havax-cb-blocking-active');
            }

            // Show banner again
            this.showBanner();
        }

        /**
         * Generate unique consent ID
         */
        generateConsentId() {
            const data = [
                Date.now(),
                Math.random().toString(36).substr(2, 9),
                navigator.userAgent,
            ].join('|');

            return this.hashString(data);
        }

        /**
         * Simple hash function
         */
        hashString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash).toString(16) + Date.now().toString(16);
        }

        /**
         * Save consent to cookie
         */
        saveConsentCookie() {
            const value = btoa(JSON.stringify(this.consent));
            const expires = new Date();
            expires.setDate(expires.getDate() + this.config.cookieExpiry);

            let cookieString = `${this.config.cookieName}=${value}`;
            cookieString += `; expires=${expires.toUTCString()}`;
            cookieString += `; path=${this.config.cookiePath}`;

            if (this.config.cookieDomain) {
                cookieString += `; domain=${this.config.cookieDomain}`;
            }

            if (this.config.cookieSecure) {
                cookieString += '; secure';
            }

            cookieString += `; samesite=${this.config.cookieSameSite}`;

            document.cookie = cookieString;
        }

        /**
         * Send consent data to PHP API endpoint
         * @param {string} action - API action (give_consent, accept_all, reject_all, withdraw_consent)
         * @param {object} data - Additional data to send
         */
        sendToApi(action, data = {}) {
            if (!this.config.apiUrl) {
                return Promise.resolve(null);
            }

            const payload = {
                action,
                ...data,
            };

            return fetch(this.config.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.dispatchEvent('havax-cb:api:success', { action, result });
                } else {
                    this.dispatchEvent('havax-cb:api:error', { action, error: result.error });
                }
                return result;
            })
            .catch(error => {
                console.error('Havax Cookie Banner API error:', error);
                this.dispatchEvent('havax-cb:api:error', { action, error: error.message });
                return null;
            });
        }

        /**
         * Check if user has given consent
         */
        hasConsent() {
            return this.consent !== null;
        }

        /**
         * Check consent for specific category
         */
        hasConsentFor(category) {
            if (!this.consent) {
                const categoryConfig = this.config.categories[category];
                return categoryConfig?.required || false;
            }
            return this.consent.accepted_categories.includes(category);
        }

        /**
         * Get accepted categories
         */
        getAcceptedCategories() {
            if (!this.consent) {
                return Object.entries(this.config.categories)
                    .filter(([key, cat]) => cat.required)
                    .map(([key]) => key);
            }
            return this.consent.accepted_categories;
        }

        /**
         * Get rejected categories
         */
        getRejectedCategories() {
            if (!this.consent) {
                return Object.entries(this.config.categories)
                    .filter(([key, cat]) => !cat.required)
                    .map(([key]) => key);
            }
            return this.consent.rejected_categories;
        }

        /**
         * Show banner
         */
        showBanner() {
            if (this.banner) {
                this.banner.classList.add('havax-cb-visible');
                this.banner.setAttribute('aria-hidden', 'false');
                this.dispatchEvent(EVENTS.BANNER_SHOWN, {});

                // In blocking mode, ensure body is locked
                if (this.config.blockingMode && !this.hasConsent()) {
                    document.body.classList.add('havax-cb-blocking-active');
                }
            }
        }

        /**
         * Hide banner
         */
        hideBanner() {
            if (this.banner) {
                this.banner.classList.remove('havax-cb-visible');
                this.banner.setAttribute('aria-hidden', 'true');
                this.dispatchEvent(EVENTS.BANNER_HIDDEN, {});

                // Unlock body if in blocking mode and consent given
                if (this.config.blockingMode && this.hasConsent()) {
                    document.body.classList.remove('havax-cb-blocking-active');
                }
            }
        }

        /**
         * Show preferences modal
         */
        showPreferences() {
            if (this.preferencesModal) {
                this.preferencesModal.classList.add('havax-cb-visible');
                this.preferencesModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('havax-cb-modal-open');
                this.dispatchEvent(EVENTS.PREFERENCES_OPENED, {});

                // Sync checkboxes with current consent
                this.syncCheckboxes();
            }
        }

        /**
         * Close preferences modal
         */
        closePreferences() {
            if (this.preferencesModal) {
                this.preferencesModal.classList.remove('havax-cb-visible');
                this.preferencesModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('havax-cb-modal-open');
                this.dispatchEvent(EVENTS.PREFERENCES_CLOSED, {});
            }
        }

        /**
         * Toggle floating panel
         */
        toggleFloatingPanel() {
            if (this.floatingPanel) {
                const isVisible = this.floatingPanel.classList.toggle('havax-cb-visible');
                if (this.floatingButton) {
                    this.floatingButton.classList.toggle('havax-cb-active', isVisible);
                }
                this.syncCheckboxes();
            }
        }

        /**
         * Sync checkboxes with current consent
         */
        syncCheckboxes() {
            const accepted = this.getAcceptedCategories();
            document.querySelectorAll('[data-category]').forEach(checkbox => {
                const category = checkbox.dataset.category;
                const categoryConfig = this.config.categories[category];

                if (categoryConfig?.required) {
                    checkbox.checked = true;
                    checkbox.disabled = true;
                } else {
                    checkbox.checked = accepted.includes(category);
                }
            });
        }

        // ==================== Script Blocker ====================

        /**
         * Setup automatic script blocking
         */
        setupScriptBlocker() {
            if (!this.config.autoBlock) return;

            // Block scripts that are already in the DOM
            this.processExistingScripts();

            // Observe for new scripts
            this.observeNewScripts();

            // Override script creation
            this.overrideScriptCreation();
        }

        /**
         * Process scripts already in DOM
         */
        processExistingScripts() {
            document.querySelectorAll('script[data-havax-cb-category]').forEach(script => {
                const category = script.dataset.havaxCbCategory;
                if (this.hasConsentFor(category)) {
                    this.activateScript(script);
                } else {
                    this.blockedScripts.push({
                        element: script,
                        category,
                        id: script.dataset.havaxCbScriptId || null,
                    });
                }
            });
        }

        /**
         * Observe DOM for new scripts
         */
        observeNewScripts() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeName === 'SCRIPT') {
                            this.processScript(node);
                        }
                    });
                });
            });

            observer.observe(document.documentElement, {
                childList: true,
                subtree: true,
            });

            this.observers.push(observer);
        }

        /**
         * Process a script element
         */
        processScript(script) {
            // Skip if already processed
            if (script.dataset.havaxCbProcessed) return;
            script.dataset.havaxCbProcessed = 'true';

            // Check if it has a category
            if (script.dataset.havaxCbCategory) {
                const category = script.dataset.havaxCbCategory;
                if (!this.hasConsentFor(category)) {
                    this.blockScript(script, category);
                }
                return;
            }

            // Auto-detect category from src
            if (script.src) {
                const category = this.detectScriptCategory(script.src);
                if (category && !this.hasConsentFor(category)) {
                    this.blockScript(script, category);
                }
            }
        }

        /**
         * Detect script category from URL
         */
        detectScriptCategory(src) {
            for (const pattern of this.config.blockerPatterns) {
                if (src.includes(pattern.pattern)) {
                    return pattern.category;
                }
            }
            return null;
        }

        /**
         * Block a script
         */
        blockScript(script, category) {
            // Store original attributes
            const originalType = script.type;
            const originalSrc = script.src;

            // Disable the script
            script.type = 'text/plain';
            script.dataset.havaxCbCategory = category;
            script.dataset.havaxCbOriginalType = originalType || 'text/javascript';

            if (originalSrc) {
                script.dataset.havaxCbOriginalSrc = originalSrc;
                script.removeAttribute('src');
            }

            this.blockedScripts.push({
                element: script,
                category,
                originalType,
                originalSrc,
            });

            this.dispatchEvent(EVENTS.SCRIPT_BLOCKED, {
                script,
                category,
                src: originalSrc,
            });

            if (typeof this.config.onScriptBlocked === 'function') {
                this.config.onScriptBlocked({ script, category, src: originalSrc });
            }
        }

        /**
         * Override native script creation to intercept dynamically added scripts
         */
        overrideScriptCreation() {
            const self = this;
            const originalSetAttribute = Element.prototype.setAttribute;

            Element.prototype.setAttribute = function(name, value) {
                if (this.nodeName === 'SCRIPT' && name === 'src') {
                    const category = self.detectScriptCategory(value);
                    if (category && !self.hasConsentFor(category)) {
                        this.type = 'text/plain';
                        this.dataset.havaxCbCategory = category;
                        this.dataset.havaxCbOriginalSrc = value;

                        self.blockedScripts.push({
                            element: this,
                            category,
                            originalSrc: value,
                        });

                        self.dispatchEvent(EVENTS.SCRIPT_BLOCKED, {
                            script: this,
                            category,
                            src: value,
                        });

                        return;
                    }
                }
                return originalSetAttribute.call(this, name, value);
            };
        }

        /**
         * Activate consented scripts
         */
        activateConsentedScripts() {
            const accepted = this.getAcceptedCategories();

            this.blockedScripts = this.blockedScripts.filter(blocked => {
                if (accepted.includes(blocked.category)) {
                    this.activateScript(blocked.element);
                    return false;
                }
                return true;
            });

            // Also process any text/plain scripts in DOM
            document.querySelectorAll('script[type="text/plain"][data-havax-cb-category]').forEach(script => {
                const category = script.dataset.havaxCbCategory;
                if (accepted.includes(category)) {
                    this.activateScript(script);
                }
            });
        }

        /**
         * Activate a blocked script
         */
        activateScript(script) {
            const category = script.dataset.havaxCbCategory;

            // Create a new script element
            const newScript = document.createElement('script');

            // Copy attributes
            Array.from(script.attributes).forEach(attr => {
                if (attr.name !== 'type' && !attr.name.startsWith('data-havax-cb')) {
                    newScript.setAttribute(attr.name, attr.value);
                }
            });

            // Set correct type
            newScript.type = script.dataset.havaxCbOriginalType || 'text/javascript';

            // Handle src or inline content
            if (script.dataset.havaxCbOriginalSrc) {
                newScript.src = script.dataset.havaxCbOriginalSrc;
            } else if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }

            // Replace original script
            script.parentNode?.replaceChild(newScript, script);

            this.loadedScripts.push({
                element: newScript,
                category,
            });

            this.dispatchEvent(EVENTS.SCRIPT_LOADED, {
                script: newScript,
                category,
            });

            if (typeof this.config.onScriptLoaded === 'function') {
                this.config.onScriptLoaded({ script: newScript, category });
            }
        }

        // ==================== Utility Methods ====================

        /**
         * Get cookie value
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        }

        /**
         * Delete cookie
         */
        deleteCookie(name) {
            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${this.config.cookiePath}`;
        }

        /**
         * Dispatch custom event
         */
        dispatchEvent(eventName, detail = {}) {
            const event = new CustomEvent(eventName, {
                detail: {
                    ...detail,
                    instance: this,
                    timestamp: new Date().toISOString(),
                },
                bubbles: true,
                cancelable: true,
            });

            document.dispatchEvent(event);
        }

        /**
         * Add event listener
         */
        on(eventName, callback) {
            // Map short names to full event names
            const eventMap = {
                'consent.given': EVENTS.CONSENT_GIVEN,
                'consent.updated': EVENTS.CONSENT_UPDATED,
                'consent.withdrawn': EVENTS.CONSENT_WITHDRAWN,
                'banner.shown': EVENTS.BANNER_SHOWN,
                'banner.hidden': EVENTS.BANNER_HIDDEN,
                'preferences.opened': EVENTS.PREFERENCES_OPENED,
                'preferences.closed': EVENTS.PREFERENCES_CLOSED,
                'script.loaded': EVENTS.SCRIPT_LOADED,
                'script.blocked': EVENTS.SCRIPT_BLOCKED,
                'category.enabled': EVENTS.CATEGORY_ENABLED,
                'category.disabled': EVENTS.CATEGORY_DISABLED,
            };

            const fullEventName = eventMap[eventName] || eventName;
            document.addEventListener(fullEventName, (e) => callback(e.detail));
            return this;
        }

        /**
         * Remove event listener
         */
        off(eventName, callback) {
            document.removeEventListener(eventName, callback);
            return this;
        }

        /**
         * Get consent data for server
         */
        getConsentData() {
            return this.consent;
        }

        /**
         * Get consent proof (hash)
         */
        getConsentProof() {
            if (!this.consent) return null;
            return btoa(JSON.stringify({
                consent_id: this.consent.consent_id,
                timestamp: this.consent.timestamp,
                accepted: this.consent.accepted_categories,
                rejected: this.consent.rejected_categories,
            }));
        }

        /**
         * Update configuration
         */
        updateConfig(newConfig) {
            this.config = { ...this.config, ...newConfig };
        }

        /**
         * Destroy instance
         */
        destroy() {
            // Remove observers
            this.observers.forEach(observer => observer.disconnect());
            this.observers = [];

            // Remove banner
            if (this.banner) {
                this.banner.remove();
            }

            this.isInitialized = false;
        }
    }

    // Expose to global scope
    window.HavaxCbBanner = HavaxCbBanner;

    // Also expose event names
    window.HavaxCbBanner.EVENTS = EVENTS;

    // Auto-initialize if config is present
    if (window.havaxCbConfig) {
        // Create instance
        window.havaxCbInstance = new HavaxCbBanner(window.havaxCbConfig);

        // Dispatch init event on next tick to allow listeners to be registered
        // This ensures user's event listeners added after this script are called
        setTimeout(() => {
            document.dispatchEvent(new CustomEvent(EVENTS.INIT, {
                detail: {
                    instance: window.havaxCbInstance,
                    consent: window.havaxCbInstance.consent,
                    timestamp: new Date().toISOString(),
                },
                bubbles: true,
                cancelable: true,
            }));
        }, 0);
    }

})(window, document);
