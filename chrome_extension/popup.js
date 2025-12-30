const DASHBOARD_URL = 'http://localhost:8000';

document.getElementById('launchBtn').addEventListener('click', () => {
    chrome.tabs.create({ url: DASHBOARD_URL });
});

async function checkStatus() {
    const statusText = document.getElementById('statusText');
    try {
        const response = await fetch(DASHBOARD_URL + '/up', { method: 'HEAD', mode: 'no-cors' });
        // 'no-cors' returns an opaque response, but if it doesn't throw, server is reachable (mostly)
        statusText.textContent = 'Online';
        statusText.style.color = 'green';
    } catch (error) {
        statusText.textContent = 'Offline (Start server)';
        statusText.style.color = 'red';
    }
}

checkStatus();
