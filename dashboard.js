document.addEventListener('DOMContentLoaded', function() {
    // Navigation handling
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const sections = document.querySelectorAll('.dashboard-section');

    function showSection(sectionId) {
        sections.forEach(section => section.style.display = 'none');
        document.getElementById(sectionId).style.display = 'block';
        navLinks.forEach(link => {
            link.classList.toggle('active', link.dataset.section === sectionId);
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showSection(this.dataset.section);
        });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-resource').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this resource?')) {
                e.preventDefault();
            }
        });
    });

    // Feedback handling
    const viewButtons = document.querySelectorAll('.view-feedback');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const feedbackId = this.getAttribute('data-id');
            fetch('get_feedback.php?id=' + feedbackId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('feedbackName').textContent = data.name;
                    document.getElementById('feedbackEmail').textContent = data.email;
                    document.getElementById('feedbackCategory').textContent = data.category;
                    document.getElementById('feedbackMessage').textContent = data.message;
                    document.getElementById('feedbackDate').textContent = new Date(data.created_at).toLocaleString();
                    
                    const markAsReadBtn = document.getElementById('markAsReadBtn');
                    if (data.status === 'New') {
                        markAsReadBtn.style.display = 'block';
                        markAsReadBtn.setAttribute('data-id', feedbackId);
                    } else {
                        markAsReadBtn.style.display = 'none';
                    }
                });
        });
    });

    // Mark feedback as read
    const markAsReadBtn = document.getElementById('markAsReadBtn');
    if (markAsReadBtn) {
        markAsReadBtn.addEventListener('click', function() {
            const feedbackId = this.getAttribute('data-id');
            fetch('update_feedback_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + feedbackId + '&status=Read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Error updating feedback status');
                }
            });
        });
    }

    // Filter feedback by category
    const feedbackFilter = document.getElementById('feedbackFilter');
    if (feedbackFilter) {
        feedbackFilter.addEventListener('change', function() {
            const category = this.value;
            const rows = document.querySelectorAll('#feedback-section table tbody tr');
            
            rows.forEach(row => {
                const rowCategory = row.querySelector('td:nth-child(3)').textContent;
                if (category === 'all' || rowCategory === category) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}); 