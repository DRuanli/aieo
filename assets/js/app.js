/**
 * Main application JavaScript for IELTS Study Tracker
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Initialize Bootstrap popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Auto-hide success alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Add confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-calculate overall score in the score form
    const scoreForm = document.querySelector('form[name="score-form"]');
    if (scoreForm) {
        const listeningInput = document.getElementById('listening');
        const readingInput = document.getElementById('reading');
        const writingInput = document.getElementById('writing');
        const speakingInput = document.getElementById('speaking');
        const overallInput = document.getElementById('overall');
        
        const calculateOverall = () => {
            const listening = parseFloat(listeningInput.value) || 0;
            const reading = parseFloat(readingInput.value) || 0;
            const writing = parseFloat(writingInput.value) || 0;
            const speaking = parseFloat(speakingInput.value) || 0;
            
            if (listening && reading && writing && speaking) {
                const overall = Math.round(((listening + reading + writing + speaking) / 4) * 2) / 2;
                overallInput.value = overall.toFixed(1);
            }
        };
        
        if (listeningInput && readingInput && writingInput && speakingInput && overallInput) {
            listeningInput.addEventListener('input', calculateOverall);
            readingInput.addEventListener('input', calculateOverall);
            writingInput.addEventListener('input', calculateOverall);
            speakingInput.addEventListener('input', calculateOverall);
        }
    }
    
    // Word count for content textarea
    const contentTextarea = document.getElementById('content');
    const wordCountDisplay = document.getElementById('word-count');
    
    if (contentTextarea && wordCountDisplay) {
        const updateWordCount = () => {
            const text = contentTextarea.value;
            const words = text.trim() ? text.trim().split(/\s+/).length : 0;
            wordCountDisplay.textContent = words;
        };
        
        contentTextarea.addEventListener('input', updateWordCount);
        updateWordCount(); // Initial count
    }
    
    // Initialize DataTables if table is present
    if ($.fn.DataTable && document.getElementById('wordTable')) {
        $('#wordTable').DataTable({
            "order": [[1, "desc"]],
            "pageLength": 25,
            "language": {
                "search": "Filter words:",
                "lengthMenu": "Show _MENU_ words per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ words"
            }
        });
    }
    
    // Tab persistence using URL hash
    const tabHash = window.location.hash;
    if (tabHash) {
        const tab = document.querySelector(`a[href="${tabHash}"]`);
        if (tab) {
            const tabInstance = new bootstrap.Tab(tab);
            tabInstance.show();
        }
    }
    
    // Update hash when tab changes
    const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabLinks.forEach(tabLink => {
        tabLink.addEventListener('shown.bs.tab', function (e) {
            window.location.hash = e.target.getAttribute('href');
        });
    });
});