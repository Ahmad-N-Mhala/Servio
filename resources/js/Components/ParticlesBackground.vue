<template>
    <div ref="container" class="fixed inset-0 z-0 overflow-hidden pointer-events-none bg-slate-50">
        <!-- Modern Mesh Gradient Blobs -->
        <div class="absolute top-[-10%] left-[-10%] w-[45%] h-[45%] rounded-full bg-emerald-400/20 blur-[120px] mix-blend-multiply animate-blob transition-all duration-1000"></div>
        <div class="absolute top-[10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-teal-300/20 blur-[130px] mix-blend-multiply animate-blob animation-delay-2000 transition-all duration-1000"></div>
        <div class="absolute bottom-[-15%] left-[15%] w-[60%] h-[60%] rounded-full bg-green-200/20 blur-[140px] mix-blend-multiply animate-blob animation-delay-4000 transition-all duration-1000"></div>
        
        <!-- Subtle noise/grid pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTAgMGg0MHY0MEgwem0yMCAyMmMxLjEgMCAyLS45IDItMnMtLjktMi0yLTItMiAuOS0yIDIgLjkgMiAyIDJ6bTEwIDBjMS4xIDAgMi0uOSAyLTJzLS45LTItMi0yLTIgLjktMiAyIC45IDIgMiAyeiIgZmlsbD0iIzEwYjk4MSIgZmlsbC1vcGFjaXR5PSIwLjA1IiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=')] opacity-60 mix-blend-overlay"></div>

        <canvas ref="canvas" class="absolute inset-0 w-full h-full opacity-80"></canvas>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const container = ref<HTMLElement | null>(null);
const canvas = ref<HTMLCanvasElement | null>(null);
let ctx: CanvasRenderingContext2D | null = null;
let animationFrameId: number;
let particles: Particle[] = [];
let mouse = { x: -1000, y: -1000 };

// Configuration for "Pro" feel
const PARTICLE_COUNT = 80; // Optimized count
const CONNECTION_DISTANCE = 0; // Disabled lines for performance
const MOUSE_RADIUS = 200; // Snappier interaction
const MOUSE_FORCE = 0.08; 

class Particle {
    x: number;
    y: number;
    baseX: number;
    baseY: number;
    size: number;
    density: number;
    color: string;
    vx: number;
    vy: number;
    z: number; // Depth factor (0.5 to 1.5)

    constructor(w: number, h: number) {
        this.x = Math.random() * w;
        this.y = Math.random() * h;
        this.baseX = this.x;
        this.baseY = this.y;
        this.z = Math.random() * 1.5 + 0.5; // Depth multiplier
        this.size = (Math.random() * 2 + 1) * this.z; // Closer particles are larger
        this.density = (Math.random() * 10 + 5) * this.z; // Closer particles react more
        this.vx = (Math.random() - 0.5) * 0.2 * this.z; // Parallax speed
        this.vy = (Math.random() - 0.5) * 0.2 * this.z;
        
        // Brand colors with depth-based opacity
        const baseOpacity = 0.2 * this.z;
        const colors = [
            `rgba(16, 185, 129, ${baseOpacity})`, // Emerald
            `rgba(52, 211, 153, ${baseOpacity})`, // Lighter Emerald
            `rgba(156, 163, 175, ${baseOpacity})`, // Gray
        ];
        this.color = colors[Math.floor(Math.random() * colors.length)];
    }

    draw() {
        if (!ctx) return;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.fill();
    }

    update() {
        // Organic Drift with Parallax
        this.baseX += this.vx;
        this.baseY += this.vy;

        // Wrap around screen
        if (canvas.value) {
            const w = canvas.value.width;
            const h = canvas.value.height;
            if (this.baseX > w + 50) this.baseX = -50;
            if (this.baseX < -50) this.baseX = w + 50;
            if (this.baseY > h + 50) this.baseY = -50;
            if (this.baseY < -50) this.baseY = h + 50;
        }

        // Mouse Interaction (Magnetic + Friction)
        const dx = mouse.x - this.x;
        const dy = mouse.y - this.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        
        if (distance < MOUSE_RADIUS) {
            const force = (MOUSE_RADIUS - distance) / MOUSE_RADIUS;
            const directionX = (dx / distance) * force * this.density * MOUSE_FORCE;
            const directionY = (dy / distance) * force * this.density * MOUSE_FORCE;
            
            this.x += directionX;
            this.y += directionY;
        } else {
            // Smooth elastic return to base position
            if (this.x !== this.baseX) {
                const dx = this.x - this.baseX;
                this.x -= dx * 0.02; // Friction factor
            }
            if (this.y !== this.baseY) {
                const dy = this.y - this.baseY;
                this.y -= dy * 0.02;
            }
        }
    }
}

const init = () => {
    if (!canvas.value) return;
    particles = [];
    const w = canvas.value.width;
    const h = canvas.value.height;
    
    // Responsive count
    const particleCount = window.innerWidth < 768 ? PARTICLE_COUNT / 2 : PARTICLE_COUNT;
    
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle(w, h));
    }
};

const animate = () => {
    if (!ctx || !canvas.value) return;
    ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
    
    // Update and Draw Particles
    for (let i = 0; i < particles.length; i++) {
        particles[i].update();
        particles[i].draw();
    }
    
    animationFrameId = requestAnimationFrame(animate);
};

const handleResize = () => {
    if (!canvas.value || !container.value) return;
    canvas.value.width = container.value.clientWidth;
    canvas.value.height = container.value.clientHeight;
    init();
};

const handleMouseMove = (e: MouseEvent) => {
    mouse.x = e.x;
    mouse.y = e.y;
};

const handleTouchMove = (e: TouchEvent) => {
    if(e.touches.length > 0) {
        mouse.x = e.touches[0].clientX;
        mouse.y = e.touches[0].clientY;
    }
};

onMounted(() => {
    if (!canvas.value || !container.value) return;
    
    ctx = canvas.value.getContext('2d');
    canvas.value.width = container.value.clientWidth;
    canvas.value.height = container.value.clientHeight;

    window.addEventListener('resize', handleResize);
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('touchmove', handleTouchMove);

    init();
    animate();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('touchmove', handleTouchMove);
    cancelAnimationFrame(animationFrameId);
});
</script>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 12s infinite alternate ease-in-out;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
