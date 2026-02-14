<template>
    <div ref="container" class="fixed inset-0 z-0 overflow-hidden pointer-events-none bg-gray-50">
        <canvas ref="canvas" class="absolute inset-0 w-full h-full"></canvas>
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
const PARTICLE_COUNT = 140; // Increased for denser background
const CONNECTION_DISTANCE = 140; // Distance to draw lines
const MOUSE_RADIUS = 250; // Larger influence radius
const MOUSE_FORCE = 0.1; // Stronger magnetic pull

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

        // Draw Constellation Connections
        // We only check particles[j] where j > i to avoid duplicate lines and self-checks
        for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance < CONNECTION_DISTANCE) {
                // Calculate opacity based on distance (closer = more opaque)
                const opacity = 1 - (distance / CONNECTION_DISTANCE);
                // Use a subtle emerald/gray mix for lines
                ctx.beginPath();
                ctx.strokeStyle = `rgba(16, 185, 129, ${opacity * 0.15})`; // Very subtle line
                ctx.lineWidth = 0.5;
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.stroke();
                ctx.closePath();
            }
        }
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
