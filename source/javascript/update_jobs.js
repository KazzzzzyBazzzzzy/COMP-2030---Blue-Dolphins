function editJob(jobId, jobDescription) {
    document.getElementById('job_id').value = jobId;
    document.getElementById('job_description').value = jobDescription;
}

document.addEventListener("DOMContentLoaded", function() {
    const updateForm = document.querySelector(".job-description-form");

    if (updateForm) {
        updateForm.addEventListener("submit", function(event) {
            const jobIdInput = document.getElementById("job_id").value.trim();
            const jobDescriptionInput = document.getElementById("job_description").value.trim();

            if (!jobIdInput || !jobDescriptionInput) {
                event.preventDefault();
                alert("Both Job ID and Job Description are required.");
            }
        });
    }
});
