// Event Step 2 JavaScript - Dynamic Layout Builder with TinyMCE Integration

// Global variables
let layoutComponents = [];
let componentCounter = 0;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Event Step 2 JavaScript loaded');
    
    // Initialize any existing components
    initializeExistingComponents();
    
    // Load existing layout data if available
    loadExistingLayoutData();
    
    // Set up event listeners
    setupEventListeners();
    
    // Listen for theme changes
    setupThemeChangeListener();
});

// Setup event listeners
function setupEventListeners() {
    // Form submission handlers
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', handleFormSubmission);
    }
    
    // Add event listeners to all textareas for layout updates
    document.addEventListener('input', function(event) {
        if (event.target.tagName === 'TEXTAREA') {
            // Update layout JSON when textarea content changes
            setTimeout(() => {
                updateLayoutJSON();
            }, 100);
        }
    });
    
    // Add event listeners to file inputs
    document.addEventListener('change', function(event) {
        if (event.target.type === 'file') {
            // Update layout JSON when files are selected
            setTimeout(() => {
                updateLayoutJSON();
            }, 100);
        }
    });
}

// Handle form submission
function handleFormSubmission(event) {
    const submitButton = event.submitter;
    
    if (submitButton && submitButton.name === 'preview' && submitButton.value === 'true') {
        // Handle preview submission
        event.preventDefault();
        previewLayouts();
    } else {
        // Handle normal submission - update layout JSON
        updateLayoutJSON();
    }
}

// Preview layouts function
function previewLayouts() {
    console.log('Previewing layouts...');
    
    // Update layout JSON before preview
    updateLayoutJSON();
    
    // Submit form to preview route
    const form = document.querySelector('form');
    if (form) {
        const formData = new FormData(form);
        formData.append('preview', 'true');
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = response.url;
            } else {
                console.error('Preview failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

// Preview poster function
function previewPoster(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Update mobile poster preview
            const mobilePreview = document.getElementById('poster-preview');
            if (mobilePreview) {
                mobilePreview.innerHTML = `<img src="${e.target.result}" alt="Poster Preview" class="w-full h-full object-cover rounded-lg">`;
            }
            
            // Update desktop poster preview
            const desktopPreview = document.getElementById('poster-preview-desktop');
            if (desktopPreview) {
                desktopPreview.innerHTML = `<img src="${e.target.result}" alt="Poster Preview" class="w-full h-full object-cover rounded-lg">`;
            }
        };
        reader.readAsDataURL(file);
    }
}

// Preview thumbnail function
function previewThumbnail(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const thumbnailBg = document.getElementById('thumbnail-bg');
            if (thumbnailBg) {
                thumbnailBg.style.backgroundImage = `url(${e.target.result})`;
                thumbnailBg.style.filter = 'grayscale(100%) brightness(0.6) contrast(1.3)';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Toggle description for mobile
function toggleDescription() {
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const fullDescription = document.getElementById('fullDescription');
    const eventDescription = document.getElementById('event-description');
    
    if (viewMoreBtn && fullDescription && eventDescription) {
        if (fullDescription.classList.contains('hidden')) {
            fullDescription.classList.remove('hidden');
            eventDescription.classList.add('hidden');
            viewMoreBtn.textContent = 'View Less';
        } else {
            fullDescription.classList.add('hidden');
            eventDescription.classList.remove('hidden');
            viewMoreBtn.textContent = 'View More';
        }
    }
}

// Toggle description for desktop
function toggleDescriptionDesktop() {
    const viewMoreBtnDesktop = document.getElementById('viewMoreBtnDesktop');
    const fullDescriptionDesktop = document.getElementById('fullDescriptionDesktop');
    const eventDescriptionDesktop = document.getElementById('event-description-desktop');
    
    if (viewMoreBtnDesktop && fullDescriptionDesktop && eventDescriptionDesktop) {
        if (fullDescriptionDesktop.classList.contains('hidden')) {
            fullDescriptionDesktop.classList.remove('hidden');
            eventDescriptionDesktop.classList.add('hidden');
            viewMoreBtnDesktop.textContent = 'View Less';
        } else {
            fullDescriptionDesktop.classList.add('hidden');
            eventDescriptionDesktop.classList.remove('hidden');
            viewMoreBtnDesktop.textContent = 'View More';
        }
    }
}

// Layout Modal Functions
function openLayoutModal() {
    const modal = document.getElementById('layoutModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeLayoutModal() {
    const modal = document.getElementById('layoutModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

function selectLayout(layoutType) {
    console.log('Selected layout:', layoutType);
    
    // Add the layout component
    addLayoutComponent(layoutType);
    
    // Close the modal
    closeLayoutModal();
}

// Add layout component function
function addLayoutComponent(layoutType) {
    componentCounter++;
    const componentId = `component-${componentCounter}`;
    
    console.log('Adding layout component:', layoutType, 'with ID:', componentId);
    
    const layoutComponentsContainer = document.getElementById('layoutComponents');
    if (!layoutComponentsContainer) {
        console.error('Layout components container not found');
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
            componentHTML = createTicketsPricingComponent(componentId);
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
        default:
            console.error('Unknown layout type:', layoutType);
            return;
    }
    
    // Create component wrapper
    const componentWrapper = document.createElement('div');
    componentWrapper.className = 'layout-component border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800';
    componentWrapper.id = componentId;
    componentWrapper.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">${getLayoutDisplayName(layoutType)}</h3>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="moveComponentUp('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <button type="button" onclick="moveComponentDown('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <button type="button" onclick="removeLayoutComponent('${componentId}')" class="p-1 text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        ${componentHTML}
    `;
    
    layoutComponentsContainer.appendChild(componentWrapper);
    
    // Initialize TinyMCE for textareas in this component
    setTimeout(() => {
        initializeTinyMCEForLayout(componentId);
    }, 1000);
    
    // Store component data
    layoutComponents.push({
        id: componentId,
        type: layoutType,
        data: {}
    });
    
    // Update layout JSON after adding component
    setTimeout(() => {
        updateLayoutJSON();
    }, 500);
    
    console.log('Layout component added successfully');
}

// Layout component creation functions
function createHeroCarouselComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter hero content..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Background Image</label>
                <input type="file" id="${componentId}-image" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300" onchange="previewLayoutImage('${componentId}', this)">
                <div id="${componentId}-image-preview" class="mt-2 hidden">
                    <img class="w-full h-32 object-cover rounded-lg" alt="Preview">
                    <p class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>
    `;
}

function createEventDetailsCardsComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Card Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter card content..."></textarea>
            </div>
        </div>
    `;
}

function createAboutTwoImagesComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">About Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter about content..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image 1</label>
                    <input type="file" id="${componentId}-image1" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300" onchange="previewLayoutImage('${componentId}-img1', this)">
                    <div id="${componentId}-img1-image-preview" class="mt-2 hidden">
                        <img class="w-full h-24 object-cover rounded-lg" alt="Preview">
                        <p class="text-sm text-gray-500 mt-1"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image 2</label>
                    <input type="file" id="${componentId}-image2" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300" onchange="previewLayoutImage('${componentId}-img2', this)">
                    <div id="${componentId}-img2-image-preview" class="mt-2 hidden">
                        <img class="w-full h-24 object-cover rounded-lg" alt="Preview">
                        <p class="text-sm text-gray-500 mt-1"></p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createSpeakersGridComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Speakers Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter speakers content..."></textarea>
            </div>
        </div>
    `;
}

function createPhotoHighlightsComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gallery Images</label>
                <input type="file" id="${componentId}-gallery" accept="image/*" multiple class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
            </div>
        </div>
    `;
}

function createFaqListComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">FAQ Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="6" placeholder="Enter FAQ content..."></textarea>
            </div>
        </div>
    `;
}

function createTicketsPricingComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pricing Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter pricing content..."></textarea>
            </div>
        </div>
    `;
}

function createFooterSimpleComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Footer Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter footer content..."></textarea>
            </div>
        </div>
    `;
}

function createLeftTextRightImageComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Text Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter text content..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                <input type="file" id="${componentId}-image" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300" onchange="previewLayoutImage('${componentId}', this)">
                <div id="${componentId}-image-preview" class="mt-2 hidden">
                    <img class="w-full h-32 object-cover rounded-lg" alt="Preview">
                    <p class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>
    `;
}

function createRightTextLeftImageComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Text Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter text content..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                <input type="file" id="${componentId}-image" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300" onchange="previewLayoutImage('${componentId}', this)">
                <div id="${componentId}-image-preview" class="mt-2 hidden">
                    <img class="w-full h-32 object-cover rounded-lg" alt="Preview">
                    <p class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>
    `;
}

function createCenterTextComponent(componentId) {
    return `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Text Content</label>
                <textarea id="${componentId}-content" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" placeholder="Enter text content..."></textarea>
            </div>
        </div>
    `;
}

// Helper functions
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
        'center-text': 'Center Text'
    };
    return names[layoutType] || layoutType;
}

function previewLayoutImage(componentId, input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`${componentId}-image-preview`);
            if (preview) {
                const img = preview.querySelector('img');
                const filename = preview.querySelector('p');
                if (img) img.src = e.target.result;
                if (filename) filename.textContent = file.name;
                preview.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    }
}

function updateLayoutText(componentId, textareaId, content) {
    const component = layoutComponents.find(c => c.id === componentId);
    if (component) {
        component.data[textareaId] = content;
    }
}

// Component management functions
function removeLayoutComponent(componentId) {
    const component = document.getElementById(componentId);
    if (component) {
        component.remove();
        
        // Remove from layoutComponents array
        layoutComponents = layoutComponents.filter(c => c.id !== componentId);
        
        // Update layout JSON after removing component
        updateLayoutJSON();
        
        console.log('Component removed:', componentId);
    }
}

function moveComponentUp(componentId) {
    const component = document.getElementById(componentId);
    const previousSibling = component.previousElementSibling;
    
    if (previousSibling) {
        component.parentNode.insertBefore(component, previousSibling);
        updateLayoutJSON();
        console.log('Component moved up:', componentId);
    }
}

function moveComponentDown(componentId) {
    const component = document.getElementById(componentId);
    const nextSibling = component.nextElementSibling;
    
    if (nextSibling) {
        component.parentNode.insertBefore(nextSibling, component);
        updateLayoutJSON();
        console.log('Component moved down:', componentId);
    }
}

function updateLayoutJSON() {
    console.log('Updating layout JSON...');
    
    // Collect all component data
    const layoutData = [];
    
    layoutComponents.forEach(component => {
        const componentElement = document.getElementById(component.id);
        if (componentElement) {
            const data = {
                layout: component.type,
                details: {}
            };
            
            // Collect textarea content (including TinyMCE content)
            const textareas = componentElement.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                const id = textarea.id.replace(`${component.id}-`, '');
                let content = textarea.value;
                
                // If TinyMCE is initialized for this textarea, get content from TinyMCE
                if (typeof tinymce !== 'undefined' && tinymce.get(textarea.id)) {
                    content = tinymce.get(textarea.id).getContent();
                }
                
                data.details[id] = content;
            });
            
            // Collect file inputs
            const fileInputs = componentElement.querySelectorAll('input[type="file"]');
            fileInputs.forEach(fileInput => {
                const id = fileInput.id.replace(`${component.id}-`, '');
                if (fileInput.files.length > 0) {
                    data.details[id] = fileInput.files[0].name;
                }
            });
            
            layoutData.push(data);
        }
    });
    
    // Update hidden input
    const hiddenInput = document.getElementById('layout_data_input');
    if (hiddenInput) {
        const jsonString = JSON.stringify(layoutData);
        hiddenInput.value = jsonString;
        console.log('Layout JSON stored in hidden input:', jsonString);
    } else {
        console.error('Hidden input for layout_data not found!');
    }
    
    console.log('Layout JSON updated with', layoutData.length, 'components:', layoutData);
    
    return layoutData;
}

// TinyMCE Integration
function initializeTinyMCEForLayout(componentId) {
    console.log('Initializing TinyMCE for component:', componentId);
    
    // Check if TinyMCE is available
    if (typeof tinymce === 'undefined') {
        console.log('TinyMCE not available yet, retrying...');
        setTimeout(() => initializeTinyMCEForLayout(componentId), 500);
        return;
    }
    
    // Find textareas in the component
    const component = document.getElementById(componentId);
    if (!component) {
        console.error('Component not found:', componentId);
        return;
    }
    
    const textareas = component.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        const textareaId = textarea.id;
        
        // Check if TinyMCE is already initialized for this textarea
        if (tinymce.get(textareaId)) {
            console.log('TinyMCE already initialized for:', textareaId);
            return;
        }
        
        console.log('Initializing TinyMCE for textarea:', textareaId);
        
        // Check if dark mode is active
        const isDarkMode = document.documentElement.classList.contains('dark') || 
                          document.body.classList.contains('dark') ||
                          window.matchMedia('(prefers-color-scheme: dark)').matches;

        tinymce.init({
            selector: `#${textareaId}`,
            height: 200,
            menubar: false,
            skin: isDarkMode ? 'oxide-dark' : 'oxide',
            content_css: isDarkMode ? 'dark' : 'default',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: isDarkMode ? 
                'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; color: #e5e7eb; background-color: #1f2937; }' :
                'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; color: #111827; background-color: #ffffff; }',
            setup: function(editor) {
                editor.on('change', function() {
                    // Update component data when content changes
                    updateLayoutText(componentId, textareaId.replace(`${componentId}-`, ''), editor.getContent());
                    // Also update the layout JSON
                    updateLayoutJSON();
                });
                
                editor.on('keyup', function() {
                    // Update layout JSON on keyup for real-time updates
                    updateLayoutJSON();
                });
            }
        });
    });
}

function initializeExistingComponents() {
    // Initialize any existing components on page load
    const existingComponents = document.querySelectorAll('.layout-component');
    existingComponents.forEach(component => {
        const componentId = component.id;
        if (componentId) {
            // Extract component type from component or data attribute
            const componentType = component.dataset.type || 'unknown';
            
            // Add to layoutComponents array if not already present
            const existingComponent = layoutComponents.find(c => c.id === componentId);
            if (!existingComponent) {
                layoutComponents.push({
                    id: componentId,
                    type: componentType,
                    data: {}
                });
            }
            
            // Initialize TinyMCE for existing components
            setTimeout(() => {
                initializeTinyMCEForLayout(componentId);
            }, 1000);
        }
    });
}

function loadExistingLayoutData() {
    console.log('Loading existing layout data...');
    console.log('window.existingLayoutData:', window.existingLayoutData);
    console.log('window.existingLayoutData type:', typeof window.existingLayoutData);
    console.log('window.existingLayoutData is array:', Array.isArray(window.existingLayoutData));
    
    // Check if there's existing layout data from the server
    if (window.existingLayoutData && Array.isArray(window.existingLayoutData) && window.existingLayoutData.length > 0) {
        console.log('Found existing layout data with', window.existingLayoutData.length, 'components:', window.existingLayoutData);
        
        // Clear any existing components first
        const existingContainer = document.getElementById('layoutComponents');
        if (existingContainer) {
            existingContainer.innerHTML = '';
        }
        layoutComponents = [];
        componentCounter = 0;
        
        // Load each component from the existing data
        window.existingLayoutData.forEach((componentData, index) => {
            console.log('Loading component', index + 1, ':', componentData);
            console.log('Component data type:', typeof componentData);
            console.log('Component data keys:', Object.keys(componentData || {}));
            
            // Add the component
            addLayoutComponentFromData(componentData);
        });
        
        // Update the layout JSON with the loaded data
        setTimeout(() => {
            updateLayoutJSON();
            console.log('Layout JSON updated after loading existing components');
        }, 2000);
        
    } else {
        console.log('No existing layout data found or data is empty');
        console.log('window.existingLayoutData type:', typeof window.existingLayoutData);
        console.log('window.existingLayoutData value:', window.existingLayoutData);
        
        // Check if it's an object that might need to be converted to array
        if (window.existingLayoutData && typeof window.existingLayoutData === 'object' && !Array.isArray(window.existingLayoutData)) {
            console.log('Layout data is an object, checking if it contains components...');
            console.log('Object keys:', Object.keys(window.existingLayoutData));
            
            // Check if it's a single component object
            if (window.existingLayoutData.layout) {
                console.log('Found single component object, converting to array');
                window.existingLayoutData = [window.existingLayoutData];
                loadExistingLayoutData(); // Recursive call with corrected data
                return;
            }
        }
    }
}

function addLayoutComponentFromData(componentData) {
    console.log('addLayoutComponentFromData called with:', componentData);
    console.log('Component data type:', typeof componentData);
    console.log('Component data keys:', Object.keys(componentData || {}));
    
    if (!componentData) {
        console.error('No component data provided');
        return;
    }
    
    if (!componentData.layout) {
        console.error('Component data missing layout property:', componentData);
        return;
    }
    
    componentCounter++;
    const componentId = `component-${componentCounter}`;
    
    console.log('Adding layout component from data:', componentData.layout, 'with ID:', componentId);
    console.log('Component data:', componentData);
    
    const layoutComponentsContainer = document.getElementById('layoutComponents');
    if (!layoutComponentsContainer) {
        console.error('Layout components container not found');
        return;
    }
    
    let componentHTML = '';
    
    // Generate component HTML based on type
    switch (componentData.layout) {
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
            componentHTML = createTicketsPricingComponent(componentId);
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
        default:
            console.error('Unknown layout type:', componentData.layout);
            return;
    }
    
    // Create component wrapper
    const componentWrapper = document.createElement('div');
    componentWrapper.className = 'layout-component border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800';
    componentWrapper.id = componentId;
    componentWrapper.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">${getLayoutDisplayName(componentData.layout)}</h3>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="moveComponentUp('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <button type="button" onclick="moveComponentDown('${componentId}')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <button type="button" onclick="removeLayoutComponent('${componentId}')" class="p-1 text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        type: componentData.layout,
        data: componentData.details || {}
    });
    
    // Load existing content into the component
    setTimeout(() => {
        loadComponentContent(componentId, componentData.details || {});
        initializeTinyMCEForLayout(componentId);
        
        // Load content again after TinyMCE is initialized
        setTimeout(() => {
            loadComponentContent(componentId, componentData.details || {});
        }, 1500);
    }, 500);
    
    console.log('Layout component loaded from data successfully');
}

function loadComponentContent(componentId, contentData) {
    console.log('Loading content for component:', componentId, contentData);
    console.log('Content data type:', typeof contentData);
    console.log('Content data keys:', Object.keys(contentData || {}));
    
    const component = document.getElementById(componentId);
    if (!component) {
        console.error('Component not found:', componentId);
        return;
    }
    
    if (!contentData || typeof contentData !== 'object') {
        console.warn('No valid content data provided for component:', componentId);
        return;
    }
    
    // Load content into textareas
    Object.keys(contentData).forEach(key => {
        const textarea = component.querySelector(`#${componentId}-${key}`);
        if (textarea) {
            const content = contentData[key] || '';
            textarea.value = content;
            console.log('Loaded content into textarea:', key, content);
            
            // If TinyMCE is available for this textarea, update it
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get(textarea.id);
                if (editor) {
                    editor.setContent(content);
                    console.log('Updated TinyMCE content for:', textarea.id);
                }
            }
        } else {
            console.warn('Textarea not found for key:', key, 'in component:', componentId);
        }
    });
}

// Force initialization of all TinyMCE instances
function forceInitAllTinyMCE() {
    console.log('Force initializing all TinyMCE instances...');
    
    if (typeof tinymce === 'undefined') {
        console.log('TinyMCE not available');
        return;
    }
    
    const allTextareas = document.querySelectorAll('textarea');
    allTextareas.forEach(textarea => {
        const textareaId = textarea.id;
        if (textareaId && !tinymce.get(textareaId)) {
            console.log('Force initializing TinyMCE for:', textareaId);
            initializeTinyMCEForLayout(textareaId.split('-')[0]);
        }
    });
}

// Manual TinyMCE initialization function for debugging
function manualInitTinyMCE() {
    console.log('Manual TinyMCE initialization triggered');
    forceInitAllTinyMCE();
}

// Setup theme change listener
function setupThemeChangeListener() {
    // Listen for theme changes via class changes on html element
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                // Theme changed, reinitialize TinyMCE instances
                setTimeout(() => {
                    reinitializeTinyMCEWithTheme();
                }, 100);
            }
        });
    });
    
    // Observe changes to the html element's class attribute
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    // Also listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
        setTimeout(() => {
            reinitializeTinyMCEWithTheme();
        }, 100);
    });
}

// Reinitialize TinyMCE instances with current theme
function reinitializeTinyMCEWithTheme() {
    console.log('Reinitializing TinyMCE instances with current theme...');
    
    if (typeof tinymce === 'undefined') {
        console.log('TinyMCE not available for reinitialization');
        return;
    }
    
    // Get all existing TinyMCE instances
    const existingInstances = tinymce.get();
    
    existingInstances.forEach(function(editor) {
        const editorId = editor.id;
        console.log('Reinitializing editor:', editorId);
        
        // Remove the existing instance
        tinymce.remove(editorId);
        
        // Reinitialize with current theme
        setTimeout(() => {
            const componentId = editorId.split('-')[0];
            initializeTinyMCEForLayout(componentId);
        }, 100);
    });
}

// Export functions for global access
window.previewPoster = previewPoster;
window.previewThumbnail = previewThumbnail;
window.toggleDescription = toggleDescription;
window.toggleDescriptionDesktop = toggleDescriptionDesktop;
window.openLayoutModal = openLayoutModal;
window.closeLayoutModal = closeLayoutModal;
window.selectLayout = selectLayout;
window.addLayoutComponent = addLayoutComponent;
window.removeLayoutComponent = removeLayoutComponent;
window.moveComponentUp = moveComponentUp;
window.moveComponentDown = moveComponentDown;
window.previewLayoutImage = previewLayoutImage;
window.updateLayoutText = updateLayoutText;
window.manualInitTinyMCE = manualInitTinyMCE;
window.forceInitAllTinyMCE = forceInitAllTinyMCE;
window.reinitializeTinyMCEWithTheme = reinitializeTinyMCEWithTheme;
window.loadExistingLayoutData = loadExistingLayoutData;
window.addLayoutComponentFromData = addLayoutComponentFromData;
window.loadComponentContent = loadComponentContent;




