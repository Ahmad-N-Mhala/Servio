<template>
    <Head>
        <title>Servio</title>
        <meta name="description" content="Servio is the all-in-one POS, KDS, and Inventory management platform designed to help restaurants eliminate food waste, control costs, and maximize profit margins." />
    </Head>
    <div :dir="locale === 'ar' ? 'rtl' : 'ltr'" class="min-h-screen bg-transparent font-sans text-gray-900 selection:bg-emerald-500/20 selection:text-emerald-700">
        
        <!-- Navbar -->
        <!-- Navbar (Transparent for video) -->
        <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <Logo class="h-10 w-auto text-emerald-600" :show-text="false" />
                        <span class="text-2xl font-extrabold tracking-tight text-gray-900">Servio</span>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-8 rtl:space-x-reverse">
                        <button @click="scrollTo('about')" class="text-sm font-semibold text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-lg transition-all duration-200">{{ $t('landing.about_us') }}</button>
                        <button @click="scrollTo('services')" class="text-sm font-semibold text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-lg transition-all duration-200">{{ getSetting('services_title') || $t('landing.our_services_title') || 'Our Services' }}</button>
                        <button @click="scrollTo('modules')" class="text-sm font-semibold text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-lg transition-all duration-200">{{ $t('landing.features') }}</button>
                        <button @click="scrollTo('pricing')" class="text-sm font-semibold text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-lg transition-all duration-200">{{ $t('landing.plans_pricing') }}</button>
                        <button v-if="getSetting('journey_maps_visible') !== false && getSetting('journey_maps_visible') !== '0' && getSetting('journey_maps_visible') !== 'false'" @click="scrollTo('journeys')" class="text-sm font-semibold text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-lg transition-all duration-200">{{ getSetting('journey_title') || $t('landing.journey_title') || 'Journey Maps' }}</button>
                        
                        <div class="flex items-center gap-4 border-l border-gray-200 pl-6 rtl:border-r rtl:border-l-0 rtl:pr-6 rtl:pl-0">
                            <!-- Language Switcher -->
                             <button 
                                @click="toggleLanguage" 
                                class="p-2 rounded-xl hover:bg-gray-100 text-gray-600 hover:text-emerald-600 transition-all duration-300 flex items-center gap-2 text-sm font-bold"
                            >
                                <span class="uppercase">{{ locale }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.546-3.131 1.457-4.341" />
                                </svg>
                            </button>

                            <a :href="route('login')" class="text-sm font-bold text-gray-700 hover:text-emerald-600 transition-colors px-2">
                                {{ $t('landing.login') || 'Log in' }}
                            </a>
                            <!-- Register CTA -->
                            <button @click.prevent="openRegisterModal()" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm tracking-wide hover:bg-emerald-500 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 shadow-xl shadow-emerald-900/20 capitalize">
                                {{ $t('landing.get_started') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center gap-4">
                         <button 
                            @click="toggleLanguage" 
                            class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-sm font-bold uppercase"
                        >
                            {{ locale }}
                        </button>
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-600 hover:text-emerald-600 transition-colors">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden bg-white/95 backdrop-blur-xl border-b border-gray-100 p-4 space-y-4 shadow-xl">
                <button @click="scrollTo('about')" class="block w-full text-left rtl:text-right text-gray-600 font-semibold hover:text-emerald-600 transition-colors">{{ $t('landing.about_us') }}</button>
                <button @click="scrollTo('services')" class="block w-full text-left rtl:text-right text-gray-600 font-semibold hover:text-emerald-600 transition-colors">{{ getSetting('services_title') || $t('landing.our_services_title') || 'Our Services' }}</button>
                <button @click="scrollTo('modules')" class="block w-full text-left rtl:text-right text-gray-600 font-semibold hover:text-emerald-600 transition-colors">{{ $t('landing.features') }}</button>
                <button @click="scrollTo('pricing')" class="block w-full text-left rtl:text-right text-gray-600 font-semibold hover:text-emerald-600 transition-colors">{{ $t('landing.plans_pricing') }}</button>
                <button v-if="getSetting('journey_maps_visible') !== false && getSetting('journey_maps_visible') !== '0' && getSetting('journey_maps_visible') !== 'false'" @click="scrollTo('journeys')" class="block w-full text-left rtl:text-right text-gray-600 font-semibold hover:text-emerald-600 transition-colors">{{ getSetting('journey_title') || $t('landing.journey_title') || 'Journey Maps' }}</button>
                <div class="pt-2 border-t border-gray-100 flex flex-col gap-3">
                    <a :href="route('login')" class="block w-full text-center px-4 py-2.5 text-gray-700 hover:bg-gray-50 border border-gray-200 font-bold rounded-xl transition-colors">{{ $t('landing.login') || 'Log in' }}</a>
                    <button @click.prevent="openRegisterModal()" class="block w-full text-center px-4 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30">{{ $t('landing.get_started') }}</button>
                </div>
            </div>
        </nav>

        <!-- Interactive Particle Background -->
        <ParticlesBackground />

        <!-- Hero Section -->
        <section class="relative pt-32 pb-40 overflow-hidden min-h-screen flex items-center z-10">
             <!-- Ambient Background Glows -->
             <div class="absolute top-1/4 left-1/10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none -z-10 animate-pulse duration-[8000ms]"></div>
             <div class="absolute bottom-1/4 right-1/10 w-[30rem] h-[30rem] bg-teal-500/10 rounded-full blur-3xl pointer-events-none -z-10 animate-pulse duration-[10000ms]"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                
                <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6 animate-fade-in-up animation-delay-100">
                    {{ getSetting('hero_title') || $t('landing.hero_title') }}
                </h1>
                
                <p class="text-lg md:text-xl text-slate-600 mb-12 max-w-3xl mx-auto leading-relaxed animate-fade-in-up animation-delay-200">
                    {{ getSetting('hero_subtitle') || $t('landing.hero_subtitle') }}
                </p>
                
                <div class="flex flex-col items-center animate-fade-in-up animation-delay-300">
                    <div class="flex flex-col sm:flex-row justify-center gap-5 mb-4">
                        <button @click.prevent="openRegisterModal()" class="px-8 py-3.5 rounded-full bg-emerald-600 text-white font-bold text-lg tracking-wide shadow-lg shadow-emerald-500/30 hover:bg-emerald-500 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300 capitalize">
                            {{ $t('landing.start_free') || 'Start For Free' }}
                        </button>
                        <button @click="scrollTo('pricing')" class="px-8 py-3.5 rounded-full bg-white text-gray-900 border border-gray-200 font-bold text-lg tracking-wide shadow-lg hover:bg-gray-50 hover:border-gray-300 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 capitalize">
                            {{ $t('landing.view_pricing') }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">{{ $t('landing.no_credit_card') }}</p>
                </div>


            </div>
        </section>



        <!-- About Us Section -->
        <section id="about" class="py-32 relative scroll-mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                    <div :class="locale === 'ar' ? 'lg:order-2' : ''" class="relative group">
                        
                        <div class="relative">
                            <!-- Dynamic Screenshot Container -->
                            <div class="relative w-full aspect-[4/3] bg-slate-900 rounded-2xl overflow-hidden shadow-lg border border-slate-850 transition-all duration-500 hover:shadow-emerald-500/10">
                                <!-- Browser UI Header -->
                                <div class="h-8 bg-gray-800 flex items-center gap-2 px-4 border-b border-gray-700">
                                    <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                                </div>

                                <template v-if="screenshots && screenshots.length > 0">
                                    <div class="relative w-full h-[calc(100%-2rem)]">
                                        <div 
                                            v-for="(shot, index) in screenshots" 
                                            :key="shot.id"
                                            class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                                            :class="currentScreenshotIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                                        >
                                            <img :src="shot.image_path" class="w-full h-full object-contain bg-gray-900" />
                                        </div>
                                    </div>

                                    <!-- Navigation Arrows -->
                                    <button 
                                        v-if="screenshots.length > 1"
                                        @click="prevScreenshot"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-3 rounded-full bg-black/50 hover:bg-black/70 text-white backdrop-blur-md border border-white/10 transition-all opacity-0 group-hover:opacity-100 transform hover:scale-110"
                                        :aria-label="$t('common.previous')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    </button>
                                    
                                    <button 
                                        v-if="screenshots.length > 1"
                                        @click="nextScreenshot"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-3 rounded-full bg-black/50 hover:bg-black/70 text-white backdrop-blur-md border border-white/10 transition-all opacity-0 group-hover:opacity-100 transform hover:scale-110"
                                        :aria-label="$t('common.next')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>

                                    <!-- Dots -->
                                    <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-20" v-if="screenshots.length > 1">
                                        <button 
                                            v-for="(_, index) in screenshots" 
                                            :key="index"
                                            @click="currentScreenshotIndex = index"
                                            class="h-1.5 rounded-full transition-all duration-300 backdrop-blur-sm"
                                            :class="currentScreenshotIndex === index ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/60 w-1.5'"
                                        ></button>
                                    </div>
                                </template>
                                
                                <img v-else src="/images/dashboard-hero.png" alt="Servio Dashboard" class="w-full h-[calc(100%-2rem)] object-contain bg-gray-900" />
                            </div>
                        </div>
                    </div>

                    <div :class="locale === 'ar' ? 'lg:order-1' : ''">
                        <div class="inline-flex items-center gap-2 mb-6">
                            <span class="w-8 h-[2px] bg-emerald-600"></span>
                            <span class="text-emerald-600 font-bold tracking-widest uppercase text-sm">{{ $t('landing.about_us') }}</span>
                        </div>
                        
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-8 leading-tight">
                            {{ getLocaleText(landingSettings?.about_us_title) || $t('landing.about_title_default') }}
                        </h2>
                        
                        <div class="prose prose-lg text-gray-600">
                             <p class="whitespace-pre-line leading-relaxed text-secondary-500">
                                {{ getLocaleText(landingSettings?.about_us_description) || $t('landing.about_us_description_default') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Services Section -->
        <section id="services" class="py-24 relative overflow-hidden z-10 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <div class="inline-flex justify-center items-center gap-2 mb-4">
                        <span class="w-8 h-[2px] bg-emerald-600"></span>
                        <span class="text-emerald-600 font-bold tracking-widest uppercase text-sm">{{ getSetting('services_title') || $t('landing.our_services_title') || 'Our Services' }}</span>
                        <span class="w-8 h-[2px] bg-emerald-600"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Software Solutions Card -->
                    <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 border border-slate-100 group">
                        <div class="w-14 h-14 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">
                            {{ getSetting('software_services_title') || $t('landing.software_services_title') || 'Software Solutions' }}
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            {{ getSetting('software_services_desc') || $t('landing.software_services_desc') || 'Our POS software solutions provide comprehensive system management tools, advanced reporting features, seamless integrations, and reliable ongoing support services.' }}
                        </p>
                    </div>

                    <!-- Hardware Installation Card -->
                    <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 border border-slate-100 group">
                        <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">
                            {{ getSetting('hardware_services_title') || $t('landing.hardware_services_title') || 'Hardware Installation' }}
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            {{ getSetting('hardware_services_desc') || $t('landing.hardware_services_desc') || 'We install and configure POS machines, bill printers, tablets, cash drawers, barcode scanners, and all required hardware to ensure a complete and ready-to-use setup.' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dashboard Explanation Section -->
        <section class="py-24 relative overflow-hidden z-10">
             
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <!-- Text Content -->
                    <div :class="locale === 'ar' ? 'lg:order-1' : ''">
                        <div class="inline-flex items-center gap-2 mb-6">
                            <span class="w-8 h-[2px] bg-emerald-600"></span>
                            <span class="text-emerald-600 font-bold tracking-widest uppercase text-sm">{{ $t('landing.dashboard') || 'Dashboard' }}</span>
                        </div>
                        
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                            {{ getSetting('dashboard_title') || 'Comprehensive Restaurant Dashboard' }}
                        </h2>
                        
                        <p class="text-xl text-gray-600 mb-10 leading-relaxed">
                            {{ getSetting('dashboard_desc') || 'Manage every aspect of your restaurant from a single, intuitive interface.' }}
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Point 1 -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">{{ getSetting('dashboard_point_1') || 'Real-time analytics' }}</h4>
                                </div>
                            </div>
                            
                            <!-- Point 2 -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">{{ getSetting('dashboard_point_2') || 'Inventory Management' }}</h4>
                                </div>
                            </div>

                            <!-- Point 3 -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-1">
                                     <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">{{ getSetting('dashboard_point_3') || 'Staff Management' }}</h4>
                                </div>
                            </div>

                            <!-- Point 4 -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">{{ getSetting('dashboard_point_4') || 'CRM & Loyalty' }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual -->
                    <div :class="locale === 'ar' ? 'lg:order-2' : ''" class="relative">
                        <!-- Dashboard Preview Card -->
                        <div class="relative bg-white rounded-2xl shadow-lg overflow-hidden border border-slate-100 transition-all duration-300 hover:shadow-xl">
                             <div class="bg-gray-50 border-b border-gray-100 p-4 flex items-center gap-2">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                             </div>
                             <!-- Use existing screenshot if available, else placeholder -->
                             <div class="aspect-[16/10] bg-gray-50 relative group">
                                <img v-if="getSetting('dashboard_image')" :src="getSetting('dashboard_image')" class="w-full h-full object-cover">
                                <img v-else-if="screenshots && screenshots.length > 0" :src="screenshots[0].image_path" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                    <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 2v20M2 10h20" />
                                    </svg>
                                    <span class="text-sm font-medium">Dashboard Preview</span>
                                </div>
                                
                                <!-- Floating Stats Cards (Decoration) -->
                                <div class="absolute bottom-6 left-6 bg-white p-4 rounded-xl shadow-lg border border-gray-100 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                                    <div class="text-xs text-gray-500 mb-1">Daily Revenue</div>
                                    <div class="text-lg font-bold text-gray-900">$2,845.00</div>
                                    <div class="text-xs text-green-500 flex items-center mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        +12.5%
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Widget Explanations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-16 pt-10 border-t border-slate-100">
                    <!-- Widget 1 -->
                    <div class="group transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center mb-4 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-base text-slate-800 mb-1.5">{{ getSetting('dash_widget_1_title') || 'Revenue Analytics' }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ getSetting('dash_widget_1_desc') }}</p>
                    </div>

                    <!-- Widget 2 -->
                    <div class="group transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center mb-4 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <h3 class="font-bold text-base text-slate-800 mb-1.5">{{ getSetting('dash_widget_2_title') || 'Live Order Tracking' }}</h3>
                         <p class="text-xs text-slate-500 leading-relaxed">{{ getSetting('dash_widget_2_desc') }}</p>
                    </div>

                    <!-- Widget 3 -->
                    <div class="group transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center mb-4 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <h3 class="font-bold text-base text-slate-800 mb-1.5">{{ getSetting('dash_widget_3_title') || 'Best Sellers' }}</h3>
                         <p class="text-xs text-slate-500 leading-relaxed">{{ getSetting('dash_widget_3_desc') }}</p>
                    </div>

                    <!-- Widget 4 -->
                    <div class="group transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center mb-4 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <h3 class="font-bold text-base text-slate-800 mb-1.5">{{ getSetting('dash_widget_4_title') || 'Payment Insights' }}</h3>
                         <p class="text-xs text-slate-500 leading-relaxed">{{ getSetting('dash_widget_4_desc') }}</p>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- How It Works Section -->
                <template v-if="getSetting('how_it_works_visible') === true || getSetting('how_it_works_visible') === 1 || getSetting('how_it_works_visible') === '1' || getSetting('how_it_works_visible') === 'true'">
                    <div class="text-center mb-20 mt-32">
                         <div class="inline-flex items-center gap-2 mb-4 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                            <span class="text-xs font-bold uppercase tracking-widest">{{ $t('landing.how_it_works_title') }}</span>
                        </div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-4">{{ getSetting('how_it_works_title') || $t('landing.how_it_works_title') }}</h2>
                        <p class="text-xl text-gray-500 max-w-2xl mx-auto">{{ getSetting('how_it_works_subtitle') || $t('landing.how_it_works_subtitle') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-32 relative">
                        <!-- Connector Lines (Desktop) -->
                        <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-gray-200 via-emerald-200 to-gray-200 z-0"></div>

                        <!-- Step 1 -->
                        <div class="relative z-10 group">
                            <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-gray-200/40 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300 hover:-translate-y-1 h-full flex flex-col items-center text-center">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 text-white flex items-center justify-center text-3xl font-black mb-6 shadow-lg shadow-gray-900/20 group-hover:scale-110 transition-transform duration-300">1</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ getSetting('step_1_title') || $t('landing.step_1_title') }}</h3>
                                <p class="text-gray-500 leading-relaxed">{{ getSetting('step_1_desc') || $t('landing.step_1_desc') }}</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="relative z-10 group">
                             <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-gray-200/40 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300 hover:-translate-y-1 h-full flex flex-col items-center text-center">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-500 text-white flex items-center justify-center text-3xl font-black mb-6 shadow-lg shadow-emerald-600/30 group-hover:scale-110 transition-transform duration-300">2</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ getSetting('step_2_title') || $t('landing.step_2_title') }}</h3>
                                <p class="text-gray-500 leading-relaxed">{{ getSetting('step_2_desc') || $t('landing.step_2_desc') }}</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="relative z-10 group">
                             <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-gray-200/40 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300 hover:-translate-y-1 h-full flex flex-col items-center text-center">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-400 text-white flex items-center justify-center text-3xl font-black mb-6 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">3</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ getSetting('step_3_title') || $t('landing.step_3_title') }}</h3>
                                <p class="text-gray-500 leading-relaxed">{{ getSetting('step_3_desc') || $t('landing.step_3_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Modules Grid -->
                <div id="modules" class="scroll-mt-24 mt-24" v-if="modules && modules.length > 0">
                    <div class="flex items-center gap-4 mb-12">
                         <span class="h-px bg-gray-200 flex-grow"></span>
                         <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">{{ $t('landing.our_modules') }}</span>
                         <span class="h-px bg-gray-200 flex-grow"></span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div 
                            v-for="module in modules" 
                            :key="module.id" 
                            class="group relative bg-slate-50/50 rounded-2xl p-6 border border-slate-100/85 transition-all duration-300 hover:bg-white hover:shadow-md hover:border-emerald-500/20"
                        >
                            <div class="relative z-10 flex flex-col items-center text-center">
                                <div class="text-4xl mb-3 transform transition-transform duration-300 group-hover:scale-115">{{ module.icon }}</div>
                                <h4 class="font-bold text-slate-800 mb-1.5 text-base">{{ getLocaleText(module.title) }}</h4>
                                <p class="text-xs text-slate-500 leading-relaxed max-w-[200px]">
                                    {{ getLocaleText(module.description) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- User Journeys Section -->
        <section id="journeys" v-if="getSetting('journey_maps_visible') !== false && getSetting('journey_maps_visible') !== '0' && getSetting('journey_maps_visible') !== 'false'" class="py-24 relative overflow-hidden z-10 bg-slate-50/30">
            <!-- Ambient backgrounds -->
            <div class="absolute top-1/2 left-1/4 w-[500px] h-[500px] bg-emerald-500/5 rounded-full blur-3xl pointer-events-none -z-10"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <div class="inline-flex items-center gap-2 mb-4 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="text-xs font-bold uppercase tracking-widest">{{ getSetting('journey_title') || $t('landing.journey_title') || 'Journey Maps' }}</span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight mb-4">
                        {{ getSetting('journey_header') || $t('landing.journey_header') || 'See How Servio Empowers Every Step' }}
                    </h2>
                    <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed">
                        {{ getSetting('journey_subheader') || $t('landing.journey_subheader') || 'Explore the step-by-step journey of restaurant staff managing operations and customers experiencing loyalty rewards.' }}
                    </p>
                </div>


                <!-- Tab switcher -->
                <div class="flex justify-center mb-16">
                    <div class="inline-flex bg-slate-100/80 p-1 rounded-2xl border border-slate-200/50 relative">
                        <!-- Switch buttons -->
                        <button 
                            @click="activeJourneyTab = 'staff'" 
                            class="px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300 flex items-center gap-2 relative z-10 focus:outline-none"
                            :class="activeJourneyTab === 'staff' ? 'bg-white text-slate-800 shadow-md' : 'text-slate-500 hover:text-slate-800'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $t('landing.journey_tab_staff') || 'Restaurant Staff Journey' }}
                        </button>
                        <button 
                            @click="activeJourneyTab = 'customer'" 
                            class="px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300 flex items-center gap-2 relative z-10 focus:outline-none"
                            :class="activeJourneyTab === 'customer' ? 'bg-white text-slate-800 shadow-md' : 'text-slate-500 hover:text-slate-800'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $t('landing.journey_tab_customer') || 'Customer & Diner Journey' }}
                        </button>
                    </div>
                </div>

                <!-- Timelines Container -->
                <div class="relative min-h-[460px] md:min-h-[380px]">
                    
                    <!-- 1. Staff Journey Timeline -->
                    <Transition
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="opacity-0 translate-y-8"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-300 ease-in absolute inset-x-0"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-8"
                    >
                        <div v-if="activeJourneyTab === 'staff'" class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
                            <!-- Horizontal dashed connector line on desktop -->
                            <div class="hidden md:block absolute top-16 left-[12.5%] right-[12.5%] h-0.5 border-t-2 border-dashed border-slate-200 z-0"></div>

                            <!-- Step 1 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 01
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.staff_step_1_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.staff_step_1_desc') }}</p>
                                <ul v-if="$t('landing.staff_step_1_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.staff_step_1_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-850 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step 2 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 02
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.staff_step_2_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.staff_step_2_desc') }}</p>
                                <ul v-if="$t('landing.staff_step_2_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.staff_step_2_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-850 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step 3 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 03
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.staff_step_3_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.staff_step_3_desc') }}</p>
                                <ul v-if="$t('landing.staff_step_3_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.staff_step_3_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-850 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step 4 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 04
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.staff_step_4_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.staff_step_4_desc') }}</p>
                                <ul v-if="$t('landing.staff_step_4_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.staff_step_4_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-855 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </Transition>

                    <!-- 2. Customer Journey Timeline -->
                    <Transition
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="opacity-0 translate-y-8"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-300 ease-in absolute inset-x-0"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-8"
                    >
                        <div v-if="activeJourneyTab === 'customer'" class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
                            <!-- Horizontal dashed connector line on desktop -->
                            <div class="hidden md:block absolute top-16 left-[12.5%] right-[12.5%] h-0.5 border-t-2 border-dashed border-slate-200 z-0"></div>

                            <!-- Step 1 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125H18c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-3.375a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 01
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.customer_step_1_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.customer_step_1_desc') }}</p>
                                <ul v-if="$t('landing.customer_step_1_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.customer_step_1_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-850 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step 2 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.25 10.5h7.5" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 02
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.customer_step_2_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.customer_step_2_desc') }}</p>
                                <ul v-if="$t('landing.customer_step_2_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.customer_step_2_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-850 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step 3 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 03
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.customer_step_3_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.customer_step_3_desc') }}</p>
                                <ul v-if="$t('landing.customer_step_3_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.customer_step_3_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-855 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step 4 -->
                            <div class="relative z-10 bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-100/40 hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group flex flex-col h-full items-center text-center">
                                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rtl:origin-right"></div>
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 relative z-10 transition-colors duration-300 group-hover:bg-emerald-500/5">
                                    <div class="w-14 h-14 rounded-xl bg-white shadow-sm text-emerald-600 flex items-center justify-center transform transition-transform duration-300 group-hover:rotate-6 group-hover:scale-105 border border-slate-100">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="inline-block text-[10px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100/50 rounded-full px-2.5 py-1 uppercase tracking-wider mb-4">
                                    {{ $t('landing.journey_step') || 'Step' }} 04
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mb-3 group-hover:text-emerald-700 transition-colors">{{ $t('landing.customer_step_4_title') }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5 flex-grow max-w-[220px]">{{ $t('landing.customer_step_4_desc') }}</p>
                                <ul v-if="$t('landing.customer_step_4_features')" class="w-full space-y-2 text-left rtl:text-right border-t border-slate-100 pt-4 mt-auto">
                                    <li v-for="(feat, idx) in $t('landing.customer_step_4_features').split('|')" :key="idx" class="flex items-center gap-2 text-[10px] font-medium text-slate-650">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100/40">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="group-hover:text-slate-855 transition-colors duration-200">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>

        <!-- Waste & Inventory Synchronization Section -->
        <section class="py-24 relative bg-gray-50 border-y border-gray-100 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                    <!-- Text Content -->
                    <div :class="locale === 'ar' ? 'lg:order-2' : 'lg:order-1'" class="order-2 relative group">
                        <div class="inline-flex items-center gap-2 mb-6 px-4 py-1.5 rounded-full bg-orange-50 text-orange-700 border border-orange-100">
                            <span class="text-xs font-bold uppercase tracking-widest">{{ $t('landing.inventory_control') || 'Inventory & Waste Control' }}</span>
                        </div>
                        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
                            {{ $t('landing.inventory_title') || 'Automated Inventory Sync' }}
                        </h2>
                        <p class="text-lg text-gray-600 leading-relaxed mb-8">
                            {{ $t('landing.inventory_desc') || 'Monitor your kitchen\'s heartbeat in real-time. Our intelligent sync perfectly couples your active inventory batches with waste tracking, automatically applying FIFO deductions and precise cost baselines. Stop guessing your losses and start maximizing yield with pinpoint financial accuracy.' }}
                        </p>
                        
                        <div class="space-y-6">
                            <!-- Feature 1 -->
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-1">{{ $t('landing.automated_fifo') || 'Automated FIFO Depletion' }}</h4>
                                    <p class="text-gray-500 text-sm leading-relaxed">{{ $t('landing.automated_fifo_desc') || 'The system knows precisely which batch corresponds to waste entries, ensuring cost models reflect literal shelf reality.' }}</p>
                                </div>
                            </div>
                            
                            <!-- Feature 2 -->
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-1">{{ $t('landing.precise_loss') || 'Precise Total Loss Tracking' }}</h4>
                                    <p class="text-gray-500 text-sm leading-relaxed">{{ $t('landing.precise_loss_desc') || 'Identify leakage trends immediately. Total financial losses are mathematically locked to the exact unit cost of the ruined ingredient.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Side -->
                    <div :class="locale === 'ar' ? 'lg:order-1' : 'lg:order-2'" class="order-1 relative group">
                        <!-- Abstract Visual Graphic -->
                        <div class="relative w-full aspect-square max-w-md mx-auto">
                            <!-- Background shapes -->
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-200 to-green-200 rounded-full blur-3xl opacity-50 group-hover:opacity-70 transition-opacity duration-500"></div>
                            
                            <div class="relative z-10 w-full h-full bg-white rounded-2xl p-8 shadow-lg border border-slate-100 flex flex-col items-center justify-center transform group-hover:scale-[1.02] transition-transform duration-300">
                                <!-- Mock UI of Waste/Inventory -->
                                <div class="w-full space-y-4">
                                    <!-- Stock bar -->
                                    <div class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                        <div class="flex justify-between items-end mb-2">
                                            <div class="text-sm font-bold text-gray-900 font-mono">Tomato (kg) <span class="text-gray-400 font-normal ml-2">FIFO Batch #102</span></div>
                                            <div class="text-xs text-green-600 font-bold">24.00 kg</div>
                                        </div>
                                        <div class="h-2 rounded-full bg-green-100 overflow-hidden"><div class="h-full bg-green-500 w-3/4"></div></div>
                                    </div>
                                    
                                    <!-- Sync Arrow -->
                                    <div class="flex justify-center -my-2 relative z-20">
                                        <div class="bg-white p-2 border border-gray-100 rounded-full shadow-sm text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Waste bar -->
                                    <div class="w-full p-4 rounded-2xl bg-red-50 border border-red-100">
                                        <div class="flex justify-between items-end mb-2">
                                            <div class="text-sm font-bold text-red-900">Waste Log</div>
                                            <div class="text-xs text-red-600 font-bold">-2.00 kg</div>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>Loss Deduction</span>
                                            <span class="font-bold font-mono text-gray-900">AED 10.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Floating Tag -->
                            <div class="absolute -bottom-6 -right-6 bg-white py-3 px-6 rounded-2xl shadow-xl border border-gray-100 flex items-center gap-3 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-100 z-20">
                                <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                                <span class="font-bold text-gray-900 tracking-wide text-sm">Live Automated Sync</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feedback Collection Section -->
        <section class="py-24 relative overflow-hidden z-10">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <!-- Content -->
                    <div :class="locale === 'ar' ? 'lg:order-2' : ''">
                        <div class="inline-flex items-center gap-2 mb-6 px-4 py-1.5 rounded-full bg-white text-emerald-700 border border-gray-100 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-widest">{{ getSetting('feedback_feature') || $t('landing.feedback_feature') || 'Customer Feedback' }}</span>
                        </div>

                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 leading-tight">
                            {{ getSetting('feedback_title') || $t('landing.feedback_title') || 'Collect Feedback & Boost Your Google Reviews' }}
                        </h2>
                        
                        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                            {{ getSetting('feedback_desc') || $t('landing.feedback_desc') || 'Gather valuable customer feedback seamlessly and automatically promote positive experiences to Google Maps.' }}
                        </p>

                        <div class="space-y-6">
                            <!-- Feature 1: Collect Feedback -->
                            <div class="flex items-start gap-4 group">
                                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">{{ getSetting('feedback_feature_1_title') || $t('landing.collect_feedback') || 'Easy Feedback Collection' }}</h3>
                                    <p class="text-gray-600">{{ getSetting('feedback_feature_1_desc') || $t('landing.collect_feedback_desc') || 'Capture customer ratings and reviews after every order with a simple, user-friendly interface.' }}</p>
                                </div>
                            </div>

                            <!-- Feature 2: Smart Google Maps Integration -->
                            <div class="flex items-start gap-4 group">
                                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">{{ getSetting('feedback_feature_2_title') || $t('landing.google_maps_boost') || 'Automatic Google Maps Boost' }}</h3>
                                    <p class="text-gray-600">{{ getSetting('feedback_feature_2_desc') || $t('landing.google_maps_feature') + $t('landing.google_maps_desc') || '4-5 star reviews are automatically directed to Google Maps, helping you build a stellar online reputation effortlessly.' }}</p>
                                </div>
                            </div>

                            <!-- Feature 3: Valuable Insights -->
                            <div class="flex items-start gap-4 group">
                                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-teal-100 flex items-center justify-center group-hover:bg-teal-200 transition-colors">
                                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">{{ getSetting('feedback_feature_3_title') || $t('landing.insights') || 'Actionable Insights' }}</h3>
                                    <p class="text-gray-600">{{ getSetting('feedback_feature_3_desc') || $t('landing.insights_desc') || 'Lower ratings provide valuable feedback to improve your service, while high ratings boost your visibility.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual -->
                    <div :class="locale === 'ar' ? 'lg:order-1' : ''" class="relative group">
                        
                        
                        <!-- Main visual card -->
                        <div class="relative bg-white rounded-2xl p-8 shadow-lg border border-slate-100 transition-all duration-300 hover:shadow-xl transform group-hover:scale-[1.02]">
                            <!-- Star ratings display -->
                            <div class="flex items-center justify-center gap-3 mb-8">
                                <svg v-for="i in 5" :key="i" class="w-12 h-12" :class="i <= 4 ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>

                            <div class="text-center mb-8">
                                <h3 class="text-3xl font-bold text-gray-900 mb-2">4.8 {{ $t('landing.rating') || 'Rating' }}</h3>
                                <p class="text-gray-600">{{ $t('landing.excellent_service') || 'Excellent service!' }}</p>
                            </div>

                            <!-- Google Maps indicator -->
                            <div class="flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200">
                                <div class="flex-shrink-0 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 text-left" :class="locale === 'ar' ? 'text-right' : ''">
                                    <p class="text-sm font-bold text-gray-900">{{ $t('landing.posted_to_google') || 'Posted to Google Maps' }}</p>
                                    <p class="text-xs text-gray-600">{{ $t('landing.automatically_shared') || 'Automatically shared with potential customers' }}</p>
                                </div>
                                <svg class="w-5 h-5 text-green-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24 relative overflow-hidden z-10">
             <!-- Ambient decorative glow -->
             <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-500/5 rounded-full blur-3xl pointer-events-none -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-20">
                      <div class="inline-flex items-center gap-2 mb-4 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100/50 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-widest">{{ $t('landing.plans_pricing') || 'Flexible Pricing' }}</span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 tracking-tight">{{ $t('landing.choose_plan') || 'Choose Your Plan' }}</h2>
                    <p class="text-xl text-slate-500 leading-relaxed max-w-xl mx-auto">{{ $t('landing.choose_plan_desc') }}</p>

                       <!-- Billing Toggle -->
                     <div class="inline-flex items-center p-1.5 rounded-2xl bg-white border border-slate-200/80 shadow-lg shadow-slate-100/50 mx-auto mt-8">
                        <button 
                            @click="billingCycle = 'monthly'"
                            :class="billingCycle === 'monthly' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900'"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200"
                        >
                            {{ $t('landing.monthly') }}
                        </button>
                        <button 
                             @click="billingCycle = 'yearly'"
                            :class="billingCycle === 'yearly' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-500 hover:text-slate-800'"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center gap-2 relative"
                        >
                            {{ $t('landing.yearly') }}
                            <span 
                                :class="billingCycle === 'yearly' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'" 
                                class="text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider transition-all duration-300 scale-95"
                            >
                                {{ $t('landing.discount_20') || 'Save 20%' }}
                            </span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                    <div 
                        v-for="plan in plans" 
                        :key="plan.id"
                        class="p-8 md:p-10 rounded-[2.5rem] transition-all duration-500 flex flex-col border w-full bg-white relative group hover:-translate-y-3 hover:scale-[1.02] h-full"
                        :class="plan.is_featured 
                            ? 'border-emerald-500 shadow-2xl shadow-emerald-500/15 z-10 scale-105 hover:scale-[1.06] ring-1 ring-emerald-500' 
                            : 'border-slate-100 shadow-xl shadow-slate-100/40 opacity-95 hover:opacity-100 hover:border-slate-200'"
                    >
                        <!-- Featured Badge -->
                        <div v-if="plan.is_featured" class="absolute -top-5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg shadow-emerald-500/30 flex items-center gap-1.5">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            {{ $t('landing.most_popular') }}
                        </div>

                        <div class="mb-6">
                            <h3 class="text-3xl font-black text-slate-900 mb-2">
                                {{ getPlanName(plan) }}
                            </h3>
                            <p class="text-sm text-slate-500 font-medium leading-relaxed">
                                {{ getPlanDescription(plan) }}
                            </p>
                        </div>

                        <div class="mb-8 flex items-baseline gap-1 flex-wrap">
                             <span class="text-5xl font-black text-slate-900 tracking-tight">
                                {{ plan.currency || $t('landing.currency') }}{{ formatPrice(billingCycle === 'monthly' ? plan.price_monthly : plan.price_yearly) }}
                             </span>
                             <span class="text-slate-400 font-bold whitespace-nowrap text-sm ml-1">
                                {{ billingCycle === 'monthly' ? $t('landing.per_month') : $t('landing.per_year') }}
                             </span>
                        </div>

                        <div class="space-y-4 mb-8 flex-grow">
                             <!-- Features List -->
                             <div v-for="(feature, idx) in plan.features" :key="idx" class="flex items-start gap-3 text-sm text-slate-600 font-medium">
                                <div class="w-5.5 h-5.5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm border border-emerald-100/50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="group-hover:text-slate-900 transition-colors duration-300 leading-normal">{{ translateFeature(feature) }}</span>
                            </div>
                        </div>

                        <button 
                            @click="openRegisterModal(plan)"
                            class="w-full py-4 rounded-xl font-bold transition-all text-center text-base transform duration-300 active:scale-[0.98]"
                            :class="plan.is_featured 
                                ? 'btn-shimmer bg-gradient-to-r from-emerald-600 to-teal-500 text-white hover:from-emerald-500 hover:to-teal-600 shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/30' 
                                : 'bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-slate-100 hover:border-slate-200'"
                        >
                            {{ $t('landing.select_plan') }}
                        </button>
                    </div>

                    <!-- Custom Plan Card -->
                    <div 
                        class="p-[2px] rounded-[2.6rem] transition-all duration-500 relative group h-full opacity-95 hover:opacity-100 hover:-translate-y-3 hover:scale-[1.02] bg-gradient-to-br from-emerald-500 via-teal-500 to-indigo-600 shadow-xl shadow-emerald-500/20 hover:shadow-indigo-500/30 w-full flex flex-col"
                    >
                        <div class="p-8 md:p-10 rounded-[2.5rem] bg-white h-full flex flex-col relative overflow-hidden">
                            <!-- Exclusive pattern background overlay -->
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/20 to-white/10 pointer-events-none"></div>
                            
                            <!-- Premium badge -->
                            <div class="absolute top-0 right-0">
                                <span class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[10px] font-black tracking-widest uppercase px-3 py-1 rounded-bl-xl rounded-tr-[2rem] shadow-sm">Enterprise</span>
                            </div>

                            <div class="mb-5 relative z-10">
                                <h3 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-700 mb-2">
                                    {{ $t('landing.custom_plan_title') }}
                                </h3>
                                <p class="text-sm text-slate-500 font-medium leading-relaxed">
                                    {{ $t('landing.custom_plan_card_desc') }}
                                </p>
                            </div>

                            <div class="mb-8 flex items-baseline gap-1 flex-wrap relative z-10 mt-2">
                                 <span class="text-4xl font-black text-slate-900 tracking-tight">
                                    {{ $t('landing.lets_talk') }}
                                 </span>
                            </div>

                            <div class="space-y-4 mb-8 flex-grow">
                                 <!-- Features List -->
                                 <div class="flex items-start gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-5.5 h-5.5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm border border-emerald-100/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="group-hover:text-slate-900 transition-colors duration-300 leading-normal">{{ $t('landing.custom_feature_1') }}</span>
                                </div>
                                <div class="flex items-start gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-5.5 h-5.5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm border border-emerald-100/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="group-hover:text-slate-900 transition-colors duration-300 leading-normal">{{ $t('landing.custom_feature_2') }}</span>
                                </div>
                                <div class="flex items-start gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-5.5 h-5.5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm border border-emerald-100/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="group-hover:text-slate-900 transition-colors duration-300 leading-normal">{{ $t('landing.custom_feature_3') }}</span>
                                </div>
                                <div class="flex items-start gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-5.5 h-5.5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm border border-emerald-100/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="group-hover:text-slate-900 transition-colors duration-300 leading-normal">{{ $t('landing.custom_feature_4') }}</span>
                                </div>
                            </div>

                            <button 
                                @click="openRegisterModal()"
                                class="w-full py-4 rounded-xl font-bold transition-all text-center text-base bg-gradient-to-r from-slate-900 to-slate-800 text-white shadow-lg shadow-slate-900/10 hover:shadow-xl hover:shadow-slate-900/25 active:scale-[0.98] mt-auto relative z-10 transform duration-300"
                            >
                                {{ $t('landing.contact_us') }}
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Plan CTA -->
                 <div class="mt-20 text-center">
                    <p class="text-gray-500 mb-4">{{ $t('landing.custom_plan_desc') }}</p>
                    <a :href="'mailto:' + (page.props.system_settings?.support_email || 'sales@kenildock.com')" class="text-emerald-600 font-bold hover:text-emerald-700 hover:underline transition-all">
                        {{ $t('landing.contact_sales') }} &rarr;
                    </a>
                </div>
            </div>
        </section>




        <!-- Footer -->
        <footer class="relative z-10 bg-gradient-to-b from-gray-900 to-black text-white overflow-hidden">
            <!-- Decorative gradient line -->
            <div class="h-1 bg-gradient-to-r from-emerald-500 via-emerald-400 to-emerald-500"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                    <!-- Brand Section -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="absolute inset-0 bg-emerald-500 blur-xl opacity-30"></div>
                                <Logo class="relative h-10 w-auto text-white" :show-text="false" />
                            </div>
                            <span class="text-2xl font-bold tracking-tight bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">Servio</span>
                        </div>
                        <p class="text-sm text-gray-400 max-w-sm leading-relaxed">
                            {{ $t('landing.hero_subtitle') }}
                        </p>
                    </div>

                    <!-- Ownership Section -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                            <div class="w-8 h-0.5 bg-gradient-to-r from-emerald-500 to-transparent"></div>
                            {{ $t('landing.about_us') }}
                        </h4>
                        <div class="space-y-4">
                            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 hover:border-emerald-500/50 transition-all duration-300">
                                <p class="text-sm text-gray-300 leading-relaxed font-medium">
                                    {{ $t('landing.owned_by') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                            <div class="w-8 h-0.5 bg-gradient-to-r from-emerald-500 to-transparent"></div>
                            {{ $t('landing.contact_us') }}
                        </h4>
                        <div class="space-y-4">
                            <a :href="'mailto:' + (page.props.system_settings?.support_email || 'support@kenildock.com')" class="group flex items-center gap-4 p-4 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:border-emerald-500/50 hover:bg-gray-800 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:shadow-emerald-500/40 group-hover:scale-110 transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0 text-start">
                                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Email</p>
                                    <p class="text-sm text-gray-200 font-medium truncate group-hover:text-white transition-colors">{{ page.props.system_settings?.support_email || 'support@kenildock.com' }}</p>
                                </div>
                            </a>
                            
                            <a :href="'tel:' + (page.props.system_settings?.support_phone || '+9715049460976')" class="group flex items-center gap-4 p-4 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:border-emerald-500/50 hover:bg-gray-800 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:shadow-emerald-500/40 group-hover:scale-110 transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0 text-start">
                                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Phone</p>
                                    <p class="text-sm text-gray-200 font-medium group-hover:text-white transition-colors text-right ltr:text-left" dir="ltr">{{ page.props.system_settings?.support_phone || '+971 50 494 60976' }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="pt-8 border-t border-gray-800">
                    <div class="flex flex-col md:flex-row justify-center items-center gap-6">
                        <div class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            &copy; {{ new Date().getFullYear() }} Servio. {{ $t('landing.copyright') }}
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Registration Modal -->
        <Modal :show="showRegisterModal" @close="showRegisterModal = false" maxWidth="md">
             <div class="px-6 py-8 sm:p-10 relative overflow-hidden bg-white">
                <!-- Background Decoration -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

                <div class="text-center mb-8 relative z-10">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mx-auto mb-6 shadow-sm border border-emerald-100 transform rotate-3 hover:rotate-0 transition-all duration-300">
                        <svg v-if="selectedPlan" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                     <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2 tracking-tight">
                         {{ selectedPlan ? $t('landing.register_interest') : ($t('landing.start_free_modal_title') || 'Start Your Free Trial') }}
                     </h3>
                     <p class="text-gray-500 font-medium text-base">
                        {{ selectedPlan ? $t('landing.for_plan', { plan: getPlanName(selectedPlan) }) : ($t('landing.start_free_modal_desc') || 'Enter your details and we will set up your trial immediately, no commitments.') }}
                     </p>
                </div>

                <form @submit.prevent="submitInterest" class="space-y-4 relative z-10">
                     <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                         <div class="sm:col-span-2">
                            <Input 
                                v-model="form.name"
                                :label="$t('landing.full_name')"
                                required
                                :error="form.errors.name"
                                class="bg-gray-50/50"
                            />
                        </div>
                         <div class="sm:col-span-2">
                            <Input 
                                v-model="form.email"
                                :label="$t('landing.email_address')"
                                type="email"
                                required
                                :error="form.errors.email"
                                class="bg-gray-50/50"
                            />
                        </div>
                        <div class="sm:col-span-1">
                            <Input 
                                v-model="form.phone"
                                :label="$t('landing.phone_number')"
                                type="tel"
                                required
                                :error="form.errors.phone"
                                class="bg-gray-50/50"
                            />
                        </div>
                         <div class="sm:col-span-1">
                            <Input 
                                v-model="form.restaurant_name"
                                :label="$t('landing.restaurant_name')"
                                required
                                :error="form.errors.restaurant_name"
                                class="bg-gray-50/50"
                            />
                        </div>
                     </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $t('landing.message') }}</label>
                        <textarea 
                            v-model="form.message"
                            rows="2"
                            class="w-full rounded-xl border-gray-200 bg-gray-50/50 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 resize-none transition-colors"
                        ></textarea>
                    </div>

                    <div class="pt-4">
                         <Button type="submit" class="w-full justify-center py-3.5 text-lg shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 font-bold tracking-wide" :loading="form.processing">
                            {{ $t('landing.submit') }}
                        </Button>
                    </div>
                </form>
             </div>
        </Modal>

        <!-- Success/Toast Notification (Simple Implementation) -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="successMessage" class="fixed bottom-0 right-0 p-6 z-50">
                <div class="bg-green-600 rounded-xl shadow-lg p-4 flex items-center gap-3 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-bold">{{ successMessage }}</p>
                </div>
            </div>
        </Transition>

        <!-- Scroll to Top Button -->
        <Transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="translate-y-10 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200 transform"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-10 opacity-0"
        >
            <button 
                v-if="showScrollTop"
                @click="scrollToTop"
                class="fixed bottom-6 right-6 rtl:right-auto rtl:left-6 z-50 p-3.5 rounded-full bg-emerald-600 text-white shadow-2xl hover:bg-emerald-500 hover:scale-110 active:scale-95 transition-all duration-300 focus:outline-none"
                aria-label="Scroll to Top"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                </svg>
            </button>
        </Transition>

    </div>
</template>

<script setup lang="ts">
import { useForm, usePage, Head } from '@inertiajs/vue3';
import { defineAsyncComponent, ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

import axios from 'axios';
import Logo from '@/Components/Logo.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

// Lazy load heavy background for better LCP
const ParticlesBackground = defineAsyncComponent(() => import('@/Components/ParticlesBackground.vue'));

interface SystemSettings {
    support_email?: string;
    support_phone?: string;
    [key: string]: any;
}

interface PageProps {
    auth: {
        user: any;
    };
    system_settings?: SystemSettings;
    flash: {
        success?: string;
        error?: string;
    };
    [key: string]: any;
}

const page = usePage<PageProps>();

const props = defineProps<{
    plans: any[];
    modules: any[];
    screenshots: any[];
    deliveryProviders: any[];
    landingSettings: Record<string, any>;
}>();

const { t, locale } = useI18n();
const route = (window as any).route;

const getSetting = (key: string) => {
    const val = props.landingSettings[key];
    if (!val) return null;
    if (Array.isArray(val)) return val;
    if (typeof val === 'object' && val !== null) {
        return val[locale.value] || val['en'] || '';
    }
    return val;
};

const parseStatValue = (val: any) => {
    if (val === undefined || val === null) return { number: 0, prefix: '', suffix: '' };
    let strVal = String(val).trim();
    
    let prefix = '';
    const prefixMatch = strVal.match(/^([<>=~]+)\s*/);
    if (prefixMatch) {
        prefix = prefixMatch[1];
        strVal = strVal.slice(prefixMatch[0].length);
    }
    
    let suffix = '';
    const suffixMatch = strVal.match(/\s*([+%KMkm]+)$/);
    if (suffixMatch) {
        suffix = suffixMatch[1];
        strVal = strVal.slice(0, strVal.length - suffixMatch[0].length);
    }
    
    const numStr = strVal.replace(/,/g, '');
    let number = parseFloat(numStr);
    if (isNaN(number)) {
        number = 0;
    } else {
        const lowerSuffix = suffix.toLowerCase();
        if (lowerSuffix.includes('m')) {
            number = number * 1000000;
            suffix = suffix.replace(/[Mm]/g, '');
        } else if (lowerSuffix.includes('k')) {
            number = number * 1000;
            suffix = suffix.replace(/[Kk]/g, '');
        }
    }
    
    return { number, prefix, suffix };
};

const statsConfig = ref({
    restaurants: { prefix: '', suffix: '+' },
    orders: { prefix: '', suffix: '+' }
});

const translateFeature = (feature: string) => {
    if (!feature) return '';
    if (feature.startsWith('plan_features.')) {
        const parts = feature.split(':');
        if (parts.length > 1) {
            const key = parts[0];
            const val = parts[1];
            return t(key, { count: val });
        }
    }
    return t(feature) !== feature ? t(feature) : feature;
};

const mobileMenuOpen = ref(false);
const billingCycle = ref<'monthly' | 'yearly'>('monthly');
const scrollTo = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
        mobileMenuOpen.value = false;
    }
};

const showRegisterModal = ref(false);
const selectedPlan = ref<any>(null);
const successMessage = ref('');
const currentScreenshotIndex = ref(0);
const activeJourneyTab = ref<'staff' | 'customer'>('staff');
const visitorCount = ref(0);
const activeRestaurants = ref(0);
let screenshotInterval: any = null;

const showScrollTop = ref(false);
const handleScroll = () => {
    showScrollTop.value = window.scrollY > 400;
};
const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Animate visitor count on mount
onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    if (props.screenshots && props.screenshots.length > 1) {
        screenshotInterval = setInterval(() => {
            currentScreenshotIndex.value = (currentScreenshotIndex.value + 1) % props.screenshots.length;
        }, 5000);
    }
    
    // Animate visitor count
    const parsedRestaurants = parseStatValue(getSetting('stats_restaurants') || '500+');
    const parsedOrders = parseStatValue(getSetting('stats_orders') || '1M+');

    statsConfig.value.restaurants.prefix = parsedRestaurants.prefix;
    statsConfig.value.restaurants.suffix = parsedRestaurants.suffix || '+';
    statsConfig.value.orders.prefix = parsedOrders.prefix;
    statsConfig.value.orders.suffix = parsedOrders.suffix || '+';

    const targetVisitors = parsedOrders.number || 1000000;
    const targetRestaurants = parsedRestaurants.number || 500;
    const duration = 2000; // 2 seconds
    const steps = 60;
    const visitorIncrement = targetVisitors / steps;
    const restaurantIncrement = targetRestaurants / steps;
    
    let currentStep = 0;
    const counterInterval = setInterval(() => {
        currentStep++;
        visitorCount.value = Math.min(Math.floor(visitorIncrement * currentStep), targetVisitors);
        activeRestaurants.value = Math.min(Math.floor(restaurantIncrement * currentStep), targetRestaurants);
        
        if (currentStep >= steps) {
            clearInterval(counterInterval);
        }
    }, duration / steps);
});


onUnmounted(() => {
    if (screenshotInterval) clearInterval(screenshotInterval);
    window.removeEventListener('scroll', handleScroll);
});

const nextScreenshot = () => {
    if (!props.screenshots || props.screenshots.length === 0) return;
    currentScreenshotIndex.value = (currentScreenshotIndex.value + 1) % props.screenshots.length;
};

const prevScreenshot = () => {
    if (!props.screenshots || props.screenshots.length === 0) return;
    currentScreenshotIndex.value = (currentScreenshotIndex.value - 1 + props.screenshots.length) % props.screenshots.length;
};

const form = useForm({
    plan_id: '',
    plan_name: '',
    name: '',
    email: '',
    phone: '',
    restaurant_name: '',
    message: ''
});


const toggleLanguage = () => {
    const newLocale = locale.value === 'en' ? 'ar' : 'en';
    
    // Quick URL patch for LaravelLocalization
    const currentPath = window.location.pathname; 
    const segments = currentPath.split('/'); 
    // Format: /en/servio/...
    // segments[0] is empty, [1] is locale, [2] is servio
    if (segments[1] && (segments[1] === 'en' || segments[1] === 'ar')) {
        segments[1] = newLocale;
        window.location.href = segments.join('/');
    } else {
         // Fallback if structure is weird, force proper structure
         window.location.href = `/${newLocale}/servio`;
    }
};

const getLocaleText = (obj: any) => {
    if (!obj) return '';
    if (typeof obj === 'string') return obj;
    return obj[locale.value] || obj['en'] || '';
};

const getPlanName = (plan: any) => {
    if (!plan) return '';

    // The backend (LandingPageController) already resolves the correct
    // locale-specific name into plan.name before sending to the frontend.
    // So ALWAYS trust plan.name first — it is already Arabic or English as needed.
    if (plan.name) return plan.name;

    return '';
};

const getPlanDescription = (plan: any) => {
    if (!plan) return '';

    // The backend (LandingPageController) already resolves the correct
    // locale-specific description into plan.description before sending to the frontend.
    // So ALWAYS trust plan.description — it is already Arabic or English as needed.
    return plan.description || '';
};

const formatPrice = (price: number | string) => {
    return Number(price).toFixed(0);
};

const openRegisterModal = (plan?: any) => {
    selectedPlan.value = plan || null;
    form.plan_id = plan ? plan.id : null;
    form.plan_name = plan ? plan.name : '';
    showRegisterModal.value = true;
};

const submitInterest = async () => {
    // 1. Construct URL
    const url = `/${locale.value}/servio/register-interest`;
    console.log("Submitting form to:", url);

    // 2. Set Loading State
    form.processing = true;
    form.clearErrors();
    successMessage.value = '';

    try {
        // 3. Post Data
        const response = await axios.post(url, form.data());

        // 4. Handle Success
        console.log("Submission Success:", response);
        
        // Check if backend returned a redirect or JSON success
        // Since we are using standard back() in controller, Axios might follow it or return the page content. 
        // We assume 200 OK means success here for simplicity, as validation 422 throws error.

        showRegisterModal.value = false;
        form.reset();
        successMessage.value = t('landing.form_success') || 'Thank you! We will contact you soon.'; // Fallback text
        
        // Auto hide success message
        setTimeout(() => {
            successMessage.value = '';
        }, 5000);

    } catch (error: any) {
        console.error("Submission Error:", error);
        
        // 5. Handle Validation Errors (422)
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                // Manually map errors if form.setError expects specific format or loop
                Object.keys(errors).forEach(key => {
                    form.setError(key as any, errors[key][0]);
                });
            }
            console.log("Validation Errors:", errors);
        } else {
            // 6. Handle General Errors
            alert("Something went wrong. Please check your connection or try again.");
        }
    } finally {
        form.processing = false;
    }
};
</script>

<style scoped>
html {
    scroll-behavior: smooth;
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}

.btn-shimmer {
    position: relative;
    overflow: hidden;
}

.btn-shimmer::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    transform: translateX(-100%);
    background-image: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.25) 20%,
        rgba(255, 255, 255, 0.5) 60%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: shimmer 3s infinite;
    content: '';
}
</style>
