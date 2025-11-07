// Globals for Step 2 layout builder
window.layoutComponents = window.layoutComponents || [];
window.componentCounter = (typeof window.componentCounter !== 'undefined') ? window.componentCounter : 0;

window.__layoutJSONDebounceTimer = window.__layoutJSONDebounceTimer || null;

// Add layout component function
function addLayoutComponent(layoutType) {
    componentCounter++;
    const componentId = `component-${componentCounter}`;
    
    // console.log('Adding layout component:', layoutType, 'with ID:', componentId);
    
    const layoutComponentsContainer = document.getElementById('layoutComponents');
    if (!layoutComponentsContainer) {
        // console.error('Layout components container not found');
        return;
    }
    
    let componentHTML = '';
    
    switch (layoutType) {
        case 'hero-carousel':
            componentHTML = createHeroCarouselComponent(componentId);
            break;
        case 'event-details-cards':
            componentHTML = createEventDetailsCardsComponent(componentId);
            break;
        case 'about-two-images':
            componentHTML = createAboutTwoImagesComponent(componentId);
            break;
        case 'speakers-grid':
            componentHTML = createSpeakersGridComponent(componentId);
            break;
        case 'photo-highlights':
            if (typeof createPhotoHighlightsComponent === 'function') {
                componentHTML = createPhotoHighlightsComponent(componentId);
            } else {
                componentHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">Photo Highlights</h4>
                            <button type="button" onclick="addPhotoItem('${componentId}')" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white hover:bg-qricket-600" aria-label="Add photo">Add Photo</button>
                        </div>
                        <div id="${componentId}-photos-container" class="space-y-3"></div>
                    </div>`;
            }
            
            // Initialize photo highlights component after adding
            setTimeout(() => {
                if (typeof window.initializePhotoHighlights === 'function') {
                    window.initializePhotoHighlights(componentId);
                }
            }, 100);
            break;
        case 'faq-list':
            componentHTML = createFaqListComponent(componentId);
            break;
        case 'tickets-pricing':
            // For tickets layout in step2, we don't require any fields; render minimal stub.
            componentHTML = `
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">This section will automatically show your event's ticket types. No fields to fill out.</div>
                <div id="${componentId}-tickets-preview" class="border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Preview uses current event tickets or temporary placeholders.</div>
                </div>
            `;
            break;
        case 'footer-simple':
            componentHTML = createFooterSimpleComponent(componentId);
            break;
        case 'left-text-right-image':
            componentHTML = createLeftTextRightImageComponent(componentId);
            break;
        case 'right-text-left-image':
            componentHTML = createRightTextLeftImageComponent(componentId);
            break;
        case 'center-text':
            componentHTML = createCenterTextComponent(componentId);
            break;
        case 'premium-hero-spotlight':
            // Use modular component
            if (typeof window.createPremiumHeroSpotlightComponent === 'function') {
                componentHTML = window.createPremiumHeroSpotlightComponent(componentId);
            } else {
                componentHTML = '<div class="text-sm text-red-500">Premium Hero Spotlight component missing</div>';
            }
            break;
        case 'premium-gallery-mosaic':
            if (typeof window.createPremiumGalleryMosaicComponent === 'function') {
                componentHTML = window.createPremiumGalleryMosaicComponent(componentId);
            } else {
                componentHTML = '<div class="text-sm text-red-500">Premium Gallery Mosaic component missing</div>';
            }
            break;
        case 'premium-schedule-timeline':
            if (typeof window.createPremiumScheduleTimelineComponent === 'function') {
                componentHTML = window.createPremiumScheduleTimelineComponent(componentId);
            } else {
                componentHTML = '<div class="text-sm text-red-500">Premium Schedule Timeline component missing</div>';
            }
            break;
        case 'premium-testimonials-slider':
            if (typeof window.createPremiumTestimonialsSliderComponent === 'function') {
                componentHTML = window.createPremiumTestimonialsSliderComponent(componentId);
            } else {
                componentHTML = '<div class="text-sm text-red-500">Premium Testimonials component missing</div>';
            }
            break;
        case 'premium-sponsors-grid':
            if (typeof window.createPremiumSponsorsGridComponent === 'function') {
                componentHTML = window.createPremiumSponsorsGridComponent(componentId);
            } else {
                componentHTML = '<div class="text-sm text-red-500">Premium Sponsors Grid component missing</div>';
            }
            break;
        // Duplicate premium cases removed (consolidated earlier in switch)
        default:
            // console.error('Unknown layout type:', layoutType);
            return;
    }
    
    // Create component wrapper
    const componentWrapper = document.createElement('div');
    componentWrapper.className = 'layout-component border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800';
    componentWrapper.id = componentId;
    componentWrapper.dataset.type = layoutType;
    componentWrapper.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">${getLayoutDisplayName(layoutType)}</h3>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="moveComponentUp('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Move component up">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <button type="button" onclick="moveComponentDown('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Move component down">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <button type="button" onclick="removeLayoutComponent('${componentId}')" class="p-1 text-red-400 hover:text-red-600" aria-label="Remove component">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        ${componentHTML}
    `;
    
    layoutComponentsContainer.appendChild(componentWrapper);
    
    // TinyMCE removed
    
    // Store component data (no required fields for tickets-pricing)
    layoutComponents.push({
        id: componentId,
        type: layoutType,
        data: {}
    });
    
    // Update layout JSON after adding component
    setTimeout(() => {
        if (typeof updateLayoutJSON === 'function') {
            updateLayoutJSON();
        } else if (typeof queueAutosave === 'function') {
            // fallback: collect minimal DOM and queue autosave
            try {
                const data = document.getElementById('layout_data_input')?.value || '[]';
                queueAutosave(data);
            } catch (_) {}
        }
    }, 200);
    
    // Hydrate preview for tickets-pricing
    if (layoutType === 'tickets-pricing') {
        try { if (typeof window.hydrateTicketsPreview === 'function') window.hydrateTicketsPreview(componentId); } catch (_) {}
    }

    // console.log('Layout component added successfully');
}

// Helper functions
function updateLayoutJSON() {
    try {
        const container = document.getElementById('layoutComponents');
        const hidden = document.getElementById('layout_data_input');
        if (!container || !hidden) return;

        const comps = Array.from(container.querySelectorAll('.layout-component'));
        const json = comps.map((el) => {
            const type = el.dataset.type || 'unknown';
            const componentId = el.id;

            // Prefer the in-memory data object which is kept in sync via updateComponentDetail
            const mem = (window.layoutComponents || []).find(c => c.id === componentId);
            const memDetails = mem && mem.data ? JSON.parse(JSON.stringify(mem.data)) : {};

            const domDetails = {};
            // 1) Named inputs (excluding file inputs)
            el.querySelectorAll('input, textarea, select').forEach((inp) => {
                if (inp.name) {
                    if (inp.type === 'file') return; // skip fakepath values
                    domDetails[inp.name] = (inp.type === 'checkbox') ? !!inp.checked : inp.value;
                }
            });
            // 2) Inputs with ids that start with `${componentId}-` map to field names without requiring name attributes
            el.querySelectorAll('input[id], textarea[id], select[id]').forEach((inp) => {
                const id = inp.id || '';
                const prefix = componentId + '-';
                if (id.indexOf(prefix) === 0) {
                    const fieldName = id.substring(prefix.length);
                    if (inp.type === 'file') return; // skip fakepath values
                    domDetails[fieldName] = (inp.type === 'checkbox') ? !!inp.checked : inp.value;
                }
            });

            // 3) Component-specific structured data (e.g., FAQs, tickets, speakers)
            if (type === 'hero-carousel') {
                const slidesContainer = el.querySelector(`#${componentId}-slides-container`);
                if (slidesContainer) {
                    const items = Array.from(slidesContainer.querySelectorAll('.slide-item'));
                    const slides = items.map((_, idx) => {
                        const title = el.querySelector(`#${componentId}-slide_title_${idx}`)?.value || '';
                        const content = el.querySelector(`#${componentId}-slide_content_${idx}`)?.value || '';
                        // Pull image from memory (normalized or flattened), do not read file inputs
                        let background_image = '';
                        let background_image_path = '';
                        if (memDetails && Array.isArray(memDetails.slides) && memDetails.slides[idx]) {
                            background_image = memDetails.slides[idx].background_image || '';
                            background_image_path = memDetails.slides[idx].background_image_path || '';
                        } else if (memDetails) {
                            const flatUrl = memDetails[`background_image_${idx}`];
                            const flatPath = memDetails[`background_image_${idx}_path`];
                            if (flatUrl) {
                                background_image = flatUrl;
                                background_image_path = flatPath || '';
                            }
                        }
                        return { title, content, background_image, background_image_path };
                    });
                    domDetails.slides = slides;
                }
                // Remove any flattened slide_* keys captured earlier from both DOM and memory
                Object.keys(domDetails).forEach((k) => { if (/^(slide_title|slide_content|background_image)(?:_path)?_\d+$/.test(k)) delete domDetails[k]; });
                if (memDetails) {
                    Object.keys(memDetails).forEach((k) => { if (/^(slide_title|slide_content|background_image)(?:_path)?_\d+$/.test(k)) delete memDetails[k]; });
                    // Normalize memory to slides so future saves remain clean
                    if (!Array.isArray(memDetails.slides) && domDetails.slides) {
                        memDetails.slides = domDetails.slides;
                    }
                }
            }
            if (type === 'faq-list') {
                const faqContainer = el.querySelector(`#${componentId}-faqs-container`);
                if (faqContainer) {
                    const items = Array.from(faqContainer.querySelectorAll('.faq-item'));
                    domDetails.faqs = items.map(item => {
                        const question = item.querySelector('.faq-question')?.value || '';
                        const answer = item.querySelector('.faq-answer')?.value || '';
                        return { question, answer };
                    });
                }
            }

            if (type === 'speakers-grid') {
                const container = el.querySelector(`#${componentId}-speakers-container`);
                if (container) {
                    const items = Array.from(container.querySelectorAll('.speaker-item'));
                    domDetails.speakers = items.map((item, idx) => {
                        const name = item.querySelector('.speaker-name')?.value || '';
                        const title = item.querySelector('.speaker-title')?.value || '';
                        const bio = item.querySelector('.speaker-bio')?.value || '';
                        // Normalize to path-only storage
                        const flatKey = `speakers_${idx}_image`;
                        const flatPathKey = `speakers_${idx}_image_path`;
                        const structured = (memDetails && Array.isArray(memDetails.speakers)) ? memDetails.speakers[idx] : undefined;
                        const structuredPath = structured && (structured.image_path || structured.image);
                        const flatPath = memDetails ? (memDetails[flatPathKey] || memDetails[flatKey]) : '';
                        const toPath = (val) => {
                            if (!val || typeof val !== 'string') return '';
                            if (val.startsWith('http')) {
                                const m = val.match(/events\/.*?\.(?:jpg|jpeg|png|webp|gif)/i);
                                return m ? m[0] : '';
                            }
                            return val;
                        };
                        const image_path = toPath(structuredPath || flatPath || '');
                        // Save only path in image field too for backward compatibility
                        const image = image_path;
                        return { name, title, bio, image, image_path };
                    });
                }
                domDetails.title = el.querySelector(`#${componentId}-title`)?.value || '';
                domDetails.subtitle = el.querySelector(`#${componentId}-subtitle`)?.value || '';
            }

            if (type === 'tickets-pricing') {
                const ticketsContainer = el.querySelector(`#${componentId}-tickets-container`);
                if (ticketsContainer) {
                    const items = Array.from(ticketsContainer.querySelectorAll('.ticket-item'));
                    domDetails.tickets = items.map(item => {
                        const name = item.querySelector('input[placeholder="General Admission"]')?.value || '';
                        const price = item.querySelector('input[placeholder="$25"]')?.value || '';
                        const description = item.querySelector('input[placeholder="Perfect for individuals"]')?.value || '';
                        const features = item.querySelector('textarea')?.value || '';
                        const popular = !!item.querySelector('input[type="checkbox"]')?.checked;
                        const buttonText = item.querySelector('input[placeholder="Select Ticket"]')?.value || '';
                        return { name, price, description, features, popular, buttonText };
                    });
                }
            }

            if (type === 'photo-highlights') {
                const container = el.querySelector(`#${componentId}-photos-container`);
                if (container) {
                    const items = Array.from(container.querySelectorAll('.photo-item'));
                    const photos = items.map((item, idx) => {
                        // Get image data from memory (stored by uploadPhotoHighlight)
                        const photoUrl = memDetails[`photo_${idx}`] || '';
                        const photoPath = memDetails[`photo_${idx}_path`] || '';
                        return {
                            url: photoUrl,
                            path: photoPath
                        };
                    }).filter(photo => photo.url); // Only include photos with URLs
                    domDetails.photos = photos;
                    
                    // Also save as image1, image2 format for easier loading
                    if (photos.length > 0) {
                        domDetails.image1 = photos[0].url;
                        domDetails.image1_path = photos[0].path;
                    }
                    if (photos.length > 1) {
                        domDetails.image2 = photos[1].url;
                        domDetails.image2_path = photos[1].path;
                    }
                }
                // Remove flattened photo_* keys to keep JSON clean
                Object.keys(domDetails).forEach((k) => { if (/^photo_\d+(_path)?$/.test(k)) delete domDetails[k]; });
                if (memDetails) {
                    Object.keys(memDetails).forEach((k) => { if (/^photo_\d+(_path)?$/.test(k)) delete memDetails[k]; });
                }
            }

            // Premium: gallery mosaic uses the same data model as photo-highlights
            if (type === 'premium-gallery-mosaic') {
                const container = el.querySelector(`#${componentId}-photos-container`);
                if (container) {
                    const items = Array.from(container.querySelectorAll('.photo-item'));
                    const photos = items.map((item, idx) => {
                        const photoUrl = memDetails[`photo_${idx}`] || '';
                        const photoPath = memDetails[`photo_${idx}_path`] || '';
                        return { url: photoUrl, path: photoPath };
                    }).filter(p => p.url);
                    domDetails.photos = photos;

                    // Also expose first two as image1/image2 for simpler loaders
                    if (photos.length > 0) {
                        domDetails.image1 = photos[0].url;
                        domDetails.image1_path = photos[0].path;
                    }
                    if (photos.length > 1) {
                        domDetails.image2 = photos[1].url;
                        domDetails.image2_path = photos[1].path;
                    }
                }
                // Clean up flattened keys from both DOM details and memory
                Object.keys(domDetails).forEach((k) => { if (/^photo_\d+(_path)?$/.test(k)) delete domDetails[k]; });
                if (memDetails) {
                    Object.keys(memDetails).forEach((k) => { if (/^photo_\d+(_path)?$/.test(k)) delete memDetails[k]; });
                }
            }

            // Premium: schedule timeline → collect rows as structured items
            if (type === 'premium-schedule-timeline') {
                const container = el.querySelector(`#${componentId}-timeline-container`);
                if (container) {
                    const rows = Array.from(container.children || []);
                    domDetails.timeline = rows.map((row) => {
                        const inputs = row.querySelectorAll('input');
                        return {
                            time: inputs[0]?.value || '',
                            item: inputs[1]?.value || '',
                        };
                    }).filter(t => t.time || t.item);
                }
            }

            // Premium: testimonials slider → collect rows
            if (type === 'premium-testimonials-slider') {
                const container = el.querySelector(`#${componentId}-testimonials`);
                if (container) {
                    const rows = Array.from(container.querySelectorAll('.grid.grid-cols-5'));
                    domDetails.testimonials = rows.map((row) => {
                        const inputs = row.querySelectorAll('input');
                        return {
                            quote: inputs[0]?.value || '',
                            author: inputs[1]?.value || '',
                        };
                    }).filter(t => t.quote || t.author);
                }
            }

            // Premium: sponsors grid → collect logo urls
            if (type === 'premium-sponsors-grid') {
                const container = el.querySelector(`#${componentId}-sponsors`);
                if (container) {
                    const boxes = Array.from(container.querySelectorAll('.sponsor-item') || []);
                    domDetails.sponsors = boxes.map((b, idx) => {
                        // path is stored as sponsor_{idx}_logo in memory
                        let logo = '';
                        if (memDetails) {
                            const k = `sponsor_${idx}_logo`;
                            logo = memDetails[k] || '';
                        }
                        // fallback: try to get from hidden field (not expected with new modular)
                        return logo ? { logo } : null;
                    }).filter(s => s && s.logo);
                }
            }

            // Premium: hero spotlight → headline/subheadline handled generically; ensure we keep image fields from memory
            if (type === 'premium-hero-spotlight') {
                if (memDetails) {
                    if (memDetails.background_image) domDetails.background_image = memDetails.background_image;
                    if (memDetails.background_image_path) domDetails.background_image_path = memDetails.background_image_path;
                }
            }

            // Merge memory with DOM; let DOM (freshly collected) win over stale memory
            // Ensure cards array persists even if only in memory
            const base = { cards: [] };
            const details = Object.assign({}, base, memDetails || {}, domDetails || {});
            return { layout: type, details };
        });
        const str = JSON.stringify(json);
        hidden.value = str;
        if (typeof queueAutosave === 'function') queueAutosave(str);
    } catch (e) {
        // console.error('updateLayoutJSON failed:', e);
    }
}
function getLayoutDisplayName(layoutType) {
    const names = {
        'hero-carousel': 'Hero Carousel',
        'event-details-cards': 'Event Details Cards',
        'about-two-images': 'About Two Images',
        'speakers-grid': 'Speakers Grid',
        'photo-highlights': 'Photo Highlights',
        'faq-list': 'FAQ List',
        'tickets-pricing': 'Tickets Pricing',
        'footer-simple': 'Footer Simple',
        'left-text-right-image': 'Left Text Right Image',
        'right-text-left-image': 'Right Text Left Image',
        'center-text': 'Center Text',
        'premium-hero-spotlight': 'Premium Hero Spotlight',
        'premium-gallery-mosaic': 'Premium Gallery Mosaic',
        'premium-schedule-timeline': 'Premium Schedule Timeline',
        'premium-testimonials-slider': 'Premium Testimonials Slider',
        'premium-sponsors-grid': 'Premium Sponsors Grid'
    };
    return names[layoutType] || layoutType;
}

// Add layout component from existing data
function addLayoutComponentFromData(componentData) {
    if (!componentData) return;
    
    componentCounter++;
    const componentId = `component-${componentCounter}`;
    const layoutType = componentData.layout || componentData.type;
    
    // console.log('Adding layout component from data:', layoutType, 'with ID:', componentId);
    
    const layoutComponentsContainer = document.getElementById('layoutComponents');
    if (!layoutComponentsContainer) {
        // console.error('Layout components container not found');
        return;
    }
    
    let componentHTML = '';
    
    switch (layoutType) {
        case 'hero-carousel':
            componentHTML = createHeroCarouselComponent(componentId);
            break;
        case 'event-details-cards':
            componentHTML = createEventDetailsCardsComponent(componentId);
            break;
        case 'about-two-images':
            componentHTML = createAboutTwoImagesComponent(componentId);
            break;
        case 'speakers-grid':
            componentHTML = createSpeakersGridComponent(componentId);
            break;
        case 'photo-highlights':
            componentHTML = createPhotoHighlightsComponent(componentId);
            break;
        case 'faq-list':
            componentHTML = createFaqListComponent(componentId);
            break;
        case 'tickets-pricing':
            // Minimal stub: no inputs required, preview hydrates from event tickets
            componentHTML = `
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">This section will automatically show your event's ticket types. No fields to fill out.</div>
                <div id="${componentId}-tickets-preview" class="border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Preview uses current event tickets or temporary placeholders.</div>
                </div>
            `;
            break;
        case 'footer-simple':
            componentHTML = createFooterSimpleComponent(componentId);
            break;
        case 'left-text-right-image':
            componentHTML = createLeftTextRightImageComponent(componentId);
            break;
        case 'right-text-left-image':
            componentHTML = createRightTextLeftImageComponent(componentId);
            break;
        case 'center-text':
            componentHTML = createCenterTextComponent(componentId);
            break;
        case 'premium-hero-spotlight':
            componentHTML = createPremiumHeroSpotlightComponent(componentId);
            break;
        case 'premium-gallery-mosaic':
            componentHTML = createPremiumGalleryMosaicComponent(componentId);
            break;
        case 'premium-schedule-timeline':
            componentHTML = createPremiumScheduleTimelineComponent(componentId);
            break;
        case 'premium-testimonials-slider':
            componentHTML = createPremiumTestimonialsSliderComponent(componentId);
            break;
        case 'premium-sponsors-grid':
            componentHTML = createPremiumSponsorsGridComponent(componentId);
            break;
        default:
            // console.error('Unknown layout type:', componentData.type);
            return;
    }
    
    // Create component wrapper
    const componentWrapper = document.createElement('div');
    componentWrapper.className = 'layout-component border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800';
    componentWrapper.id = componentId;
    componentWrapper.dataset.type = layoutType;
    componentWrapper.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">${getLayoutDisplayName(layoutType)}</h3>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="moveComponentUp('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Move component up">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <button type="button" onclick="moveComponentDown('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Move component down">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <button type="button" onclick="removeLayoutComponent('${componentId}')" class="p-1 text-red-400 hover:text-red-600" aria-label="Remove component">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        ${componentHTML}
    `;
    
    layoutComponentsContainer.appendChild(componentWrapper);
    
    // Store component data
    layoutComponents.push({
        id: componentId,
        type: layoutType,
        data: componentData.details || componentData.data || {}
    });
    
    // Populate form fields with existing data
    setTimeout(() => {
        populateComponentData(componentId, componentData.details || componentData.data || {});
        // Post-populate image previews for specific components
        try {
            if (layoutType === 'about-two-images' && typeof window.hydrateAboutTwoImages === 'function') {
                window.hydrateAboutTwoImages(componentId, componentData.details || componentData.data || {});
            }
            if (layoutType === 'tickets-pricing' && typeof window.hydrateTicketsPreview === 'function') {
                window.hydrateTicketsPreview(componentId);
            }
            if (layoutType === 'photo-highlights') {
                const container = document.getElementById(`${componentId}-photos-container`);
                if (container) {
                    container.innerHTML = '';
                    
                    // Handle both old format (flattened keys) and new format (photos array)
                    const photos = [];
                    const details = componentData.details || {};
                    
                    if (Array.isArray(details.photos)) {
                        photos.push(...details.photos);
                    } else {
                        // Handle flattened photo_0, photo_1, etc. format
                        let idx = 0;
                        while (details[`photo_${idx}`]) {
                            photos.push({
                                url: details[`photo_${idx}`],
                                path: details[`photo_${idx}_path`] || ''
                            });
                            idx++;
                        }
                    }
                    
                    // Also handle simple image1, image2 format
                    if (photos.length === 0) {
                        if (details.image1) {
                            photos.push({
                                url: details.image1,
                                path: details.image1_path || ''
                            });
                        }
                        if (details.image2) {
                            photos.push({
                                url: details.image2,
                                path: details.image2_path || ''
                            });
                        }
                    }
                    
                    // console.log('Photo highlights photos to render:', photos);
                    
                    // Render photo items using the helper function
                    photos.forEach((photo, idx) => {
                        // console.log(`Processing photo ${idx}:`, photo);
                        
                        // Create photo data with original URL
                        const photoDataWithUrl = {
                            ...photo,
                            url: photo.url
                        };
                        
                        const photoItemHTML = renderPhotoFields(componentId, idx, photoDataWithUrl);
                        container.insertAdjacentHTML('beforeend', photoItemHTML);
                        
                        // Store only the relative path, not the signed URL (to avoid redundancy and expiration issues)
                        if (photo.path) {
                            updateComponentDetail(componentId, `photo_${idx}`, photo.path);
                            updateComponentDetail(componentId, `photo_${idx}_path`, photo.path);
                            
                            // Also store as image1, image2 format for easier loading
                            if (idx === 0) {
                                updateComponentDetail(componentId, 'image1', photo.path);
                                updateComponentDetail(componentId, 'image1_path', photo.path);
                            } else if (idx === 1) {
                                updateComponentDetail(componentId, 'image2', photo.path);
                                updateComponentDetail(componentId, 'image2_path', photo.path);
                            }
                        }
                        
                        // Load image with fresh URL API
                        loadPhotoWithFreshUrl(componentId, idx, photo.url, photo.path);
                    });
                    
                    // If no photos, add one empty item
                    if (photos.length === 0) {
                        addPhotoItemFallback(componentId);
                    } else {
                        // Update grid layout after loading existing photos with multiple attempts
                        [200, 500, 1000].forEach((delay, attempt) => {
                            setTimeout(() => {
                                // console.log(`Updating photo grid classes for component ${componentId} (attempt ${attempt + 1})`);
                                updatePhotoGridClasses(componentId);
                            }, delay);
                        });
                    }
                }
            }
            if (layoutType === 'speakers-grid') {
                const container = document.getElementById(`${componentId}-speakers-container`);
                // hydrate section title/subtitle
                const d = componentData.details || {};
                const titleEl = document.getElementById(`${componentId}-title`);
                const subEl = document.getElementById(`${componentId}-subtitle`);
                if (titleEl) titleEl.value = d.title || d.section_title || '';
                if (subEl) subEl.value = d.subtitle || '';
                if (container && Array.isArray(d.speakers)) {
                    container.innerHTML = '';
                    (d.speakers).forEach((sp, idx) => {
                        const wrapper = document.createElement('div');
                        if (typeof window.renderSpeakerFields === 'function') {
                            wrapper.innerHTML = window.renderSpeakerFields(componentId, idx);
                            const node = wrapper.firstElementChild;
                            container.appendChild(node);
                            setTimeout(()=>{
                                node.querySelector('.speaker-name').value = sp.name || '';
                                node.querySelector('.speaker-title').value = sp.title || '';
                                node.querySelector('.speaker-bio').value = sp.bio || '';
                                
                                // Load speaker image if it exists
                                const rawUrl = sp.image || sp[`speakers_${idx}_image`];
                                const rawPath = sp.image_path || sp[`speakers_${idx}_image_path`] || (rawUrl && !/^https?:\/\//.test(rawUrl) ? rawUrl : '');
                                if (rawUrl || rawPath) {
                                    const preview = document.getElementById(`${componentId}-speakers_${idx}_image-preview`);
                                    if (preview) {
                                        const img = preview.querySelector('img');
                                        const label = preview.querySelector('p');
                                        const setImg = (u)=>{ if (img) img.src = u; preview.classList.remove('hidden'); };
                                        const eventId = window.currentEventId;
                                        if (rawPath && eventId) {
                                            fetch('/organizer/events/get-fresh-image-url', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                                                body: JSON.stringify({ path: rawPath.includes('events/')? rawPath : `events/${eventId}/layoutimages/${rawPath}`, event_id: parseInt(eventId) })
                                            }).then(r=>r.json()).then(d=>{
                                                if (d && d.success && d.image_url) setImg(d.image_url); else if (rawUrl) setImg(rawUrl);
                                            }).catch(()=>{ if (rawUrl) setImg(rawUrl); });
                                        } else if (rawUrl) {
                                            if (!/^https?:\/\//.test(rawUrl)) {
                                                if (eventId) {
                                                    setImg(`https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/events/${eventId}/layoutimages/${rawUrl}`);
                                                } else {
                                                    const normalized = rawUrl.startsWith('events/') ? rawUrl : `events/${rawUrl}`;
                                                    setImg(`https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${normalized}`);
                                                }
                                            } else {
                                                setImg(rawUrl);
                                            }
                                        }
                                        if (label) label.textContent = 'Speaker Photo';
                                        // Store in memory for proper saving
                                        updateComponentDetail(componentId, `speakers_${idx}_image`, rawUrl || rawPath || '');
                                    }
                                }
                            }, 0);
                        }
                    });
                }
            }
            // Hydrate premium: sponsors grid
            if (layoutType === 'premium-sponsors-grid' && typeof window.hydratePremiumSponsorsGrid === 'function') {
                const details = componentData.details || componentData.data || {};
                window.hydratePremiumSponsorsGrid(componentId, details);
            }
            // Handle center-text hydration
            if (layoutType === 'center-text') {
                const details = componentData.details || componentData.data || {};
                const titleEl = document.getElementById(`${componentId}-title`);
                const contentEl = document.getElementById(`${componentId}-content`);
                if (titleEl) titleEl.value = details.title || '';
                if (contentEl) contentEl.value = details.content || '';
            }

            // Handle premium-hero-spotlight hydration via module
            if (layoutType === 'premium-hero-spotlight' && typeof window.hydratePremiumHeroSpotlight === 'function') {
                const details = componentData.details || {};
                window.hydratePremiumHeroSpotlight(componentId, details);
            }
            // Hydrate premium: gallery mosaic
            if (layoutType === 'premium-gallery-mosaic') {
                const container = document.getElementById(`${componentId}-photos-container`);
                if (container) {
                    container.innerHTML = '';
                    const details = componentData.details || {};
                    const photos = Array.isArray(details.photos) ? details.photos : [];
                    photos.forEach((photo, idx) => {
                        const html = renderPhotoFields(componentId, idx, { url: photo.url });
                        container.insertAdjacentHTML('beforeend', html);
                        updateComponentDetail(componentId, `photo_${idx}`, photo.path || photo.url || '');
                        updateComponentDetail(componentId, `photo_${idx}_path`, photo.path || '');
                        loadPhotoWithFreshUrl(componentId, idx, photo.url, photo.path);
                    });
                    if (photos.length === 0) addPhotoItemFallback(componentId);
                    [200, 500, 1000].forEach(d => setTimeout(() => updatePhotoGridClasses(componentId), d));
                }
            }

            // Hydrate premium: schedule timeline
            if (layoutType === 'premium-schedule-timeline') {
                const container = document.getElementById(`${componentId}-timeline-container`);
                if (container) {
                    // Ensure header labels exist for components created before labels were added
                    const parent = container.parentElement;
                    if (parent && !parent.querySelector(`[data-timeline-header="${componentId}"]`)) {
                        const header = document.createElement('div');
                        header.setAttribute('data-timeline-header', componentId);
                        header.className = 'grid grid-cols-5 gap-2 text-xs text-gray-500 dark:text-gray-400';
                        header.innerHTML = `
                            <div class="col-span-2">Time:</div>
                            <div class="col-span-3">Activity / Description:</div>
                        `;
                        parent.insertBefore(header, container);
                    }
                    const items = (componentData.details || {}).timeline || [];
                    items.forEach((it) => {
                        const row = document.createElement('div');
                        row.className = 'grid grid-cols-5 gap-2';
                        row.innerHTML = `
                            <input type='text' class='col-span-2 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Time (e.g., 10:00)' value='${it.time || ''}'>
                            <input type='text' class='col-span-3 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Item (e.g., Opening)' value='${it.item || ''}'>
                        `;
                        container.appendChild(row);
                    });
                    // Second pass after layout settles
                    setTimeout(() => {
                        if (container.children.length === 0 && items.length > 0) {
                            items.forEach((it) => {
                                const row = document.createElement('div');
                                row.className = 'grid grid-cols-5 gap-2';
                                const rowIndex = container.querySelectorAll('.grid').length;
                                row.innerHTML = `
                                    <label for="${componentId}-schedule-time-${rowIndex}" class="sr-only">Time</label>
                                    <input id="${componentId}-schedule-time-${rowIndex}" type='text' class='col-span-2 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Time (e.g., 10:00)' value='${it.time || ''}'>
                                    <label for="${componentId}-schedule-item-${rowIndex}" class="sr-only">Item</label>
                                    <input id="${componentId}-schedule-item-${rowIndex}" type='text' class='col-span-3 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Item (e.g., Opening)' value='${it.item || ''}'>
                                `;
                                container.appendChild(row);
                            });
                        }
                    }, 200);
                }
            }

            // Hydrate premium: testimonials slider
            if (layoutType === 'premium-testimonials-slider') {
                const container = document.getElementById(`${componentId}-testimonials`);
                if (container) {
                    const items = (componentData.details || {}).testimonials || [];
                    items.forEach((it) => {
                        const row = document.createElement('div');
                        row.className = 'grid grid-cols-5 gap-2';
                        row.innerHTML = `
                            <input type='text' class='col-span-4 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Quote text' value='${it.quote || ''}'>
                            <input type='text' class='col-span-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Author' value='${it.author || ''}'>
                        `;
                        container.appendChild(row);
                    });
                }
            }

            // (removed legacy inline sponsors hydration; now handled by window.hydratePremiumSponsorsGrid above)
            // Handle left-text-right-image image loading
            if (layoutType === 'left-text-right-image') {
                // console.log('Loading left-text-right-image component with data:', componentData);
                const data = componentData.details || componentData.data || {};
                // console.log('Extracted data for hydration:', data);
                // console.log('All available keys in data:', Object.keys(data));
                
                // Inline hydration approach (like photo highlights)
                const imageUrl = data.image_url || data.image || data.imageUrl;
                // console.log('Found image URL for left-text-right-image:', imageUrl);
                
                // Delegate image loading to the dedicated function in left-text-right-image.js
                // This ensures proper signed URL generation for R2 images
                if (typeof window.hydrateLeftTextRightImage === 'function') {
                    // console.log('Calling hydrateLeftTextRightImage function');
                    window.hydrateLeftTextRightImage(componentId, data);
                } else {
                    // console.error('hydrateLeftTextRightImage function not found');
                }
            }
            // Handle right-text-left-image image loading
            if (layoutType === 'right-text-left-image' && typeof window.hydrateRightTextLeftImage === 'function') {
                window.hydrateRightTextLeftImage(componentId, componentData.details || componentData.data || {});
            }
            // Handle hero-carousel image loading
            if (layoutType === 'hero-carousel' && typeof window.hydrateHeroCarousel === 'function') {
                // console.log('Loading hero-carousel component with data:', componentData);
                window.hydrateHeroCarousel(componentId, componentData.details || componentData.data || {});
            }
            // Handle about-two-images image loading
            if (layoutType === 'about-two-images') {
                // console.log('Loading about-two-images component with data:', componentData);
                const data = componentData.details || componentData.data || {};
                // console.log('About-two-images data keys:', Object.keys(data));
                
                // Delegate image loading to the dedicated function in about-two-images.js
                if (typeof window.hydrateAboutTwoImages === 'function') {
                    // console.log('Calling hydrateAboutTwoImages function');
                    window.hydrateAboutTwoImages(componentId, data);
                } else {
                    // console.error('hydrateAboutTwoImages function not found');
                }
            }
        } catch(_){}
    }, 100);
    
    // console.log('Layout component added from data successfully');
}

// Populate component form fields with existing data
function populateComponentData(componentId, data) {
    if (!data) return;
    
    // Populate all input fields (excluding file inputs)
    const inputs = document.querySelectorAll(`#${componentId} input, #${componentId} textarea, #${componentId} select`);
    inputs.forEach(input => {
        const fieldName = input.id.replace(`${componentId}-`, '');
        if (data[fieldName] !== undefined && input.type !== 'file') {
            if (input.type === 'checkbox') {
                input.checked = data[fieldName];
            } else {
                input.value = data[fieldName];
            }
        }
    });
    
    // Handle special cases for complex components
    if (Array.isArray(data.cards)) {
        const container = document.querySelector(`#${componentId} #cards_${componentId}`);
        if (container) {
            container.innerHTML = '';
            // Ensure icon datalist
            try { ensureIconDatalistLoaded(); } catch(_){}
            data.cards.forEach((card, idx) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'card-item border border-gray-200 dark:border-gray-600 rounded-lg p-3';
                wrapper.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label for="${componentId}-card-icon-${idx}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon</label>
                            <input id="${componentId}-card-icon-${idx}" list="icon_list_datalist" type="text" value="${card.icon || 'calendar'}" onchange="updateCardDetail('${componentId}', ${idx}, 'icon', this.value)" class="block w-full text-sm bg-white text-gray-900 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="calendar">
                        </div>
                        <div>
                            <label for="${componentId}-card-title-${idx}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
                            <input id="${componentId}-card-title-${idx}" type="text" value="${card.title || ''}" onchange="updateCardDetail('${componentId}', ${idx}, 'title', this.value)" class="block w-full text-sm bg-white text-gray-900 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Title">
                        </div>
                        <div>
                            <label for="${componentId}-card-content-${idx}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Content</label>
                            <input id="${componentId}-card-content-${idx}" type="text" value="${card.content || ''}" onchange="updateCardDetail('${componentId}', ${idx}, 'content', this.value)" class="block w-full text-sm bg-white text-gray-900 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Content">
                        </div>
                    </div>
                `;
                container.appendChild(wrapper);
            });
        }
    }
    if (data.slides && Array.isArray(data.slides)) {
        // Handle hero carousel slides
        const container = document.getElementById(`${componentId}-slides-container`);
        if (container) {
            container.innerHTML = '';
            data.slides.forEach((slide, index) => {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = renderHeroSlideFields(componentId, index);
                container.appendChild(wrapper.firstElementChild);
                
                // Populate slide data
                setTimeout(() => {
                    const slideInputs = document.querySelectorAll(`#${componentId}-slide_title_${index}, #${componentId}-slide_content_${index}`);
                    slideInputs.forEach(input => {
                        const fieldName = input.id.replace(`${componentId}-slide_`, '').replace(`_${index}`, '');
                        if (slide[fieldName] !== undefined) {
                            input.value = slide[fieldName];
                        }
                    });
                    // Preview background image if provided
                    const url = slide.background_image || '';
                    if (url) {
                        const previewWrap = document.getElementById(`${componentId}-background_image_${index}-preview`);
                        if (previewWrap) {
                            previewWrap.classList.remove('hidden');
                            const img = previewWrap.querySelector('img');
                            const cap = previewWrap.querySelector('p');
                            if (img) img.src = url;
                            const path = slide.background_image_path || '';
                            if (cap) cap.textContent = (path.split('/').pop()) || '';
                        }
                    }
                }, 50);
            });
        }
    } else {
        // Flattened hero data: slide_title_0, slide_content_0, background_image_0, etc.
        const keys = Object.keys(data || {});
        const slideIdxs = new Set();
        keys.forEach(k => {
            const m = k.match(/^(slide_title|slide_content|background_image)(?:_path)?_(\d+)$/);
            if (m) slideIdxs.add(parseInt(m[2], 10));
        });
        if (slideIdxs.size > 0) {
            const container = document.getElementById(`${componentId}-slides-container`);
            if (container) {
                const idxs = Array.from(slideIdxs).sort((a,b)=>a-b);
                container.innerHTML = '';
                const slidesMem = [];
                idxs.forEach(index => {
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = renderHeroSlideFields(componentId, index);
                    container.appendChild(wrapper.firstElementChild);
                    setTimeout(() => {
                        const titleEl = document.getElementById(`${componentId}-slide_title_${index}`);
                        const contentEl = document.getElementById(`${componentId}-slide_content_${index}`);
                        if (titleEl) titleEl.value = data[`slide_title_${index}`] || '';
                        if (contentEl) contentEl.value = data[`slide_content_${index}`] || '';
                        const url = data[`background_image_${index}`];
                        if (url) {
                            const previewWrap = document.getElementById(`${componentId}-background_image_${index}-preview`);
                            if (previewWrap) {
                                previewWrap.classList.remove('hidden');
                                const img = previewWrap.querySelector('img');
                                const cap = previewWrap.querySelector('p');
                                if (img) img.src = url;
                                const path = data[`background_image_${index}_path`] || '';
                                if (cap) cap.textContent = (path.split('/').pop()) || '';
                            }
                        }
                    }, 50);
                    slidesMem.push({
                        title: data[`slide_title_${index}`] || '',
                        content: data[`slide_content_${index}`] || '',
                        background_image: data[`background_image_${index}`] || '',
                        background_image_path: data[`background_image_${index}_path`] || ''
                    });
                });
                // Normalize memory so subsequent saves use slides array
                try {
                    const comp = (window.layoutComponents || []).find(c => c.id === componentId);
                    if (comp) {
                        comp.data = Object.assign({}, comp.data || {}, { slides: slidesMem });
                    }
                } catch(_){ }
            }
        }
    }
    
    // Handle FAQ items
    if (data.faqs && Array.isArray(data.faqs)) {
        const container = document.getElementById(`${componentId}-faqs-container`);
        if (container) {
            container.innerHTML = '';
            data.faqs.forEach((faq, index) => {
                const faqItem = document.createElement('div');
                faqItem.className = 'faq-item border border-gray-200 dark:border-gray-600 rounded-lg p-4';
                faqItem.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="${componentId}-faq-question-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Question</label>
                            <input id="${componentId}-faq-question-${index}" type="text" class="faq-question w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="What should I bring to the event?" value="${faq.question || ''}">
                        </div>
                        <div>
                            <label for="${componentId}-faq-answer-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Answer</label>
                            <textarea id="${componentId}-faq-answer-${index}" class="faq-answer w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="2" placeholder="Please bring a valid ID and your ticket.">${faq.answer || ''}</textarea>
                        </div>
                    </div>
                    <button type="button" onclick="removeFaqItem(this)" class="mt-2 text-red-600 hover:text-red-800 text-sm" aria-label="Remove FAQ item">Remove</button>
                `;
                container.appendChild(faqItem);
            });
        }
    }
    
    // Handle ticket items
    if (data.tickets && Array.isArray(data.tickets)) {
        const container = document.getElementById(`${componentId}-tickets-container`);
        if (container) {
            container.innerHTML = '';
            data.tickets.forEach((ticket, index) => {
                const ticketItem = document.createElement('div');
                ticketItem.className = 'ticket-item border border-gray-200 dark:border-gray-600 rounded-lg p-4';
                ticketItem.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="${componentId}-ticket-name-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ticket Name</label>
                            <input id="${componentId}-ticket-name-${index}" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="General Admission" value="${ticket.name || ''}">
                        </div>
                        <div>
                            <label for="${componentId}-ticket-price-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Price</label>
                            <input id="${componentId}-ticket-price-${index}" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="$25" value="${ticket.price || ''}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="${componentId}-ticket-description-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                        <input id="${componentId}-ticket-description-${index}" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Perfect for individuals" value="${ticket.description || ''}">
                    </div>
                    <div class="mt-3">
                        <label for="${componentId}-ticket-features-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Features (one per line)</label>
                        <textarea id="${componentId}-ticket-features-${index}" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3" placeholder="Event access&#10;Welcome materials&#10;Networking opportunities">${ticket.features || ''}</textarea>
                    </div>
                    <div class="mt-3 flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="${componentId}-ticket-popular-${index}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" ${ticket.popular ? 'checked' : ''}>
                            <label for="${componentId}-ticket-popular-${index}" class="ml-2 text-xs text-gray-600 dark:text-gray-400">Most Popular</label>
                        </div>
                        <div>
                            <label for="${componentId}-ticket-button-${index}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Button Text</label>
                            <input id="${componentId}-ticket-button-${index}" type="text" class="w-32 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Select Ticket" value="${ticket.buttonText || ''}">
                        </div>
                    </div>
                    <button type="button" onclick="removeTicketItem(this)" class="mt-2 text-red-600 hover:text-red-800 text-sm" aria-label="Remove ticket item">Remove</button>
                `;
                container.appendChild(ticketItem);
            });
        }
    }
}

// Remove layout component
function removeLayoutComponent(componentId) {
    const component = document.getElementById(componentId);
    if (component) {
        component.remove();
        
        // Remove from layoutComponents array
        const index = layoutComponents.findIndex(comp => comp.id === componentId);
        if (index > -1) {
            layoutComponents.splice(index, 1);
        }
        
        // Update layout JSON
        updateLayoutJSON();
        
        // console.log('Layout component removed:', componentId);
    }
}

// Move component up
function moveComponentUp(componentId) {
    const component = document.getElementById(componentId);
    if (component && component.previousElementSibling) {
        component.parentNode.insertBefore(component, component.previousElementSibling);
        updateLayoutJSON();
    }
}

// Move component down
function moveComponentDown(componentId) {
    const component = document.getElementById(componentId);
    if (component && component.nextElementSibling) {
        component.parentNode.insertBefore(component.nextElementSibling, component);
        updateLayoutJSON();
    }
}

// Update component detail
function updateComponentDetail(componentId, field, value) {
    const component = layoutComponents.find(comp => comp.id === componentId);
    if (component) {
        component.data[field] = value;
        updateLayoutJSON();
    }
}

// duplicate helpers moved to modular files (faq-list, tickets-pricing, footer-simple, event-details-cards, layout-photo-utils) have been removed from here.

// Expose key APIs to global for init scripts
window.addLayoutComponent = window.addLayoutComponent || addLayoutComponent;
window.addLayoutComponentFromData = window.addLayoutComponentFromData || addLayoutComponentFromData;
window.updateLayoutJSON = window.updateLayoutJSON || updateLayoutJSON;
window.updateComponentDetail = window.updateComponentDetail || updateComponentDetail;
// photo helpers are exposed by layout-photo-utils.js

// Minimal input/change listener to keep premium components in sync
if (!window.__layoutInputsHooked) {
    window.__layoutInputsHooked = true;
    const handler = () => {
        try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
    };
    document.addEventListener('input', (e) => {
        const lc = document.getElementById('layoutComponents');
        if (lc && lc.contains(e.target)) handler();
    });
    document.addEventListener('change', (e) => {
        const lc = document.getElementById('layoutComponents');
        if (lc && lc.contains(e.target)) handler();
    });
}
