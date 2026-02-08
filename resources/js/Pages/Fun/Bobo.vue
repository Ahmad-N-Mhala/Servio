<template>
    <div class="min-h-screen bg-pink-100 flex items-center justify-center p-4 overflow-hidden relative">
        <!-- Floating Hearts Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div v-for="n in 20" :key="n" 
                 class="absolute text-pink-300 opacity-50 animate-float"
                 :style="{
                     left: `${Math.random() * 100}%`,
                     top: `${Math.random() * 100}%`,
                     fontSize: `${Math.random() * 2 + 1}rem`,
                     animationDuration: `${Math.random() * 3 + 2}s`
                 }">
                ❤️
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl shadow-xl text-center max-w-md w-full border-4 border-pink-200 relative z-10">
            <div class="mb-8">
                <span class="text-6xl animate-bounce inline-block">🧸</span>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-bold text-pink-600 mb-8 font-serif leading-tight">
                Will you be my<br/>Valentine?
            </h1>

            <div class="flex flex-col md:flex-row items-center justify-center gap-6 h-32">
                <!-- Yes Button -->
                <button 
                    @click="handleYes"
                    class="px-8 py-3 bg-green-500 hover:bg-green-600 text-white text-xl font-bold rounded-full transform hover:scale-110 transition-all shadow-lg hover:shadow-green-200"
                >
                    Yes! 💖
                </button>

                <!-- No Button (The tricky one) -->
                <button 
                    ref="noBtn"
                    @mouseenter="moveButton"
                    @click="moveButton"
                    :style="noBtnStyle"
                    class="px-8 py-3 bg-red-400 text-white text-xl font-bold rounded-full shadow-lg transition-all duration-200"
                    :class="{ 'absolute': isMoved }"
                >
                    No 😢
                </button>
            </div>
        </div>

        <!-- Success Modal -->
        <Modal :show="showModal" @close="showModal = false" maxWidth="sm">
            <div class="p-6 text-center bg-gradient-to-b from-pink-50 to-white rounded-lg">
                <div class="text-6xl mb-4 animate-pulse">
                    💏
                </div>
                <h2 class="text-2xl font-bold text-pink-600 mb-4">
                    Yay! I knew it! 🎉
                </h2>
                <p class="text-gray-700 text-lg italic mb-6">
                    "{{ randomMessage }}"
                </p>
                <div class="flex justify-center">
                    <button 
                        @click="showModal = false"
                        class="px-6 py-2 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-full transition-colors"
                    >
                        Love you! ❤️
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const showModal = ref(false);
const isMoved = ref(false);
const noBtnStyle = ref({});
const noBtn = ref(null);

const messages = [
    "You've stolen my heart! 💘",
    "We are meant to be! 🌟",
    "My heart beats only for you! 💓",
    "You are my favorite notification! 📱",
    "Every love story is beautiful, but ours is my favorite. 📖",
    "You had me at hello. 👋",
    "Together is a wonderful place to be. 🏡",
    "You make my dopamine levels go silly! 🧪"
];

const randomMessage = computed(() => {
    return messages[Math.floor(Math.random() * messages.length)];
});

const handleYes = () => {
    showModal.value = true;
    confettiEffect();
};

const moveButton = () => {
    isMoved.value = true;
    
    // Confined to the card which is max-w-md (approx 448px)
    // We want it to stay "around the box"
    
    const cardWidth = 400; // rough internal width
    const cardHeight = 350; // rough internal height
    
    // Random position within the card boundaries
    const randomX = Math.random() * (cardWidth - 100); // 100 is approx btn width
    const randomY = Math.random() * (cardHeight - 50); // 50 is approx btn height

    noBtnStyle.value = {
        position: 'absolute',
        left: `${randomX}px`,
        top: `${randomY}px`,
        transition: 'all 0.2s ease-out',
        zIndex: 50
    };
};

const confettiEffect = () => {
    // Simple JS confetti could go here, or we just rely on the modal pop
    // For now, let's stick to the modal as requested
};
</script>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
