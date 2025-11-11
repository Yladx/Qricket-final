@php($cd = $certificateData ?? [])
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-4">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">Certificate Details</h1>
            <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">Set organization, theme, logos, recipient and signatories.</p>
        </div>
    </div>

    <!-- Organization -->
    <div>
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-1">Organization</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Organization Line 1</label>
            <input id="org_line1_input" name="org_line1" type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['org_line1'] ?? '' }}">
        </div>
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Organization Line 2</label>
            <input id="org_line2_input" name="org_line2" type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['org_line2'] ?? '' }}">
        </div>
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">School/Organization Name</label>
            <input id="org_school_input" name="org_school" type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['org_school'] ?? '' }}">
        </div>
        </div>
    </div>

    <!-- Theme & Logos -->
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-3">Theme & Logos</h3>
        
        <!-- Certificate Color Customization - Inline -->
        <div>
            <div class="text-xs font-medium text-gray-900 dark:text-gray-100 mb-2">Certificate Color Customization</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Primary Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="theme_primary" name="theme_primary" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $cd['theme_primary'] ?? '#0b2f6b' }}">
                        <input type="text" id="theme_primary-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $cd['theme_primary'] ?? '#0b2f6b' }}" placeholder="#0b2f6b">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Secondary Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="theme_secondary" name="theme_secondary" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $cd['theme_secondary'] ?? '#092557' }}">
                        <input type="text" id="theme_secondary-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $cd['theme_secondary'] ?? '#092557' }}" placeholder="#092557">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Border Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="theme_border" name="theme_border" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $cd['theme_border'] ?? '#0b2f6b' }}">
                        <input type="text" id="theme_border-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $cd['theme_border'] ?? '#0b2f6b' }}" placeholder="#0b2f6b">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Logos - Next Line -->
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Logos</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium">Left Logo</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 3MB</div>
                            <div class="mt-0.5 flex items-center gap-1">
                                <img id="logo_left_preview" src="{{ $cd['logo_left_url'] ?? '' }}" class="w-5 h-5 rounded object-contain border border-gray-200 dark:border-gray-700 {{ empty($cd['logo_left_url']) ? 'hidden' : '' }}" alt="Left logo preview">
                                <span id="logo_left_status" class="text-xs text-gray-500 dark:text-gray-400">{{ empty($cd['logo_left_url']) ? 'No file' : 'Uploaded' }}</span>
                                @if(!empty($cd['logo_left_url']))
                                <button type="button" onclick="removeLogo('left')" class="ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" title="Remove logo">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <input type="file" id="logo_left_file" accept="image/*" class="hidden" onchange="uploadLogo('left')">
                    <span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>
                </label>
                <label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium">Right Logo</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 3MB</div>
                            <div class="mt-0.5 flex items-center gap-1">
                                <img id="logo_right_preview" src="{{ $cd['logo_right_url'] ?? '' }}" class="w-5 h-5 rounded object-contain border border-gray-200 dark:border-gray-700 {{ empty($cd['logo_right_url']) ? 'hidden' : '' }}" alt="Right logo preview">
                                <span id="logo_right_status" class="text-xs text-gray-500 dark:text-gray-400">{{ empty($cd['logo_right_url']) ? 'No file' : 'Uploaded' }}</span>
                                @if(!empty($cd['logo_right_url']))
                                <button type="button" onclick="removeLogo('right')" class="ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" title="Remove logo">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <input type="file" id="logo_right_file" accept="image/*" class="hidden" onchange="uploadLogo('right')">
                    <span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Recipient -->
    <div>
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-1">Recipient</h3>
        <div class="grid grid-cols-1">
            <div>
                <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Recipient Preview Name</label>
                @php($hasLogo = !empty($cd['logo_left_url']) || !empty($cd['logo_right_url']))
                <input id="recipient_name_input" name="recipient_name" @if(!$hasLogo) disabled @endif type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['recipient_name'] ?? '' }}" placeholder="Upload a logo first, then enter name">
            </div>
        </div>
    </div>

    <!-- Signatories -->
    <div>
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-1">Signatories</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Upload signature images for each signatory below</p>
    </div>

    <!-- Signatory Details -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100">Signatory Details</h3>
            <button type="button" id="add-signatory-btn" class="px-3 py-1.5 text-xs bg-qricket-500 text-white rounded-md hover:bg-qricket-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Signatory
            </button>
        </div>
        <div id="signatory-details-container" class="space-y-3">
            <!-- Signatory 1 (default) -->
            <div class="signatory-item border border-gray-200 dark:border-gray-700 rounded-lg p-3" data-signatory="1">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300">Signatory 1</h4>
                    <button type="button" class="remove-signatory-btn text-red-500 hover:text-red-700 text-xs hidden" onclick="window.removeSignatory(1)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Name</label>
                        <input id="sig_1_name_input" name="sig_1_name" type="text" maxlength="36" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['sig_1_name'] ?? $cd['sig_left_name'] ?? '' }}" placeholder="Max 36 characters">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Title</label>
                        <input id="sig_1_title_input" name="sig_1_title" type="text" maxlength="28" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['sig_1_title'] ?? $cd['sig_left_title'] ?? '' }}" placeholder="Max 28 characters">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Signature Image</label>
            <label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                                <div class="text-xs font-medium">Signature 1</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 2MB</div>
                                <div class="mt-0.5 flex items-center gap-1">
                                    <img id="sig_1_preview" src="{{ !empty($cd['sig_1_signature']) ? asset('storage/' . $cd['sig_1_signature']) : '' }}" class="w-5 h-5 rounded object-contain border border-gray-200 dark:border-gray-700 {{ empty($cd['sig_1_signature']) ? 'hidden' : '' }}" alt="Signature 1 preview">
                                    <span id="sig_1_status" class="text-xs text-gray-500 dark:text-gray-400">{{ !empty($cd['sig_1_signature']) ? 'Uploaded' : 'No file' }}</span>
                                    @if(!empty($cd['sig_1_signature']))
                                    <button type="button" id="sig_1_remove_btn" class="ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" title="Remove signature">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                    </div>
                </div>
                        <input type="file" id="sig_1_file" accept="image/*" class="hidden">
                <span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>
            </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex items-center justify-end">
        <span id="cert_autosave_status" class="text-xs text-gray-500 dark:text-gray-400"></span>
    </div>
</div>
