function editJob(jobId, machine, employee, jobsDescription) {
    document.getElementById('job_id').value = jobId;
    document.getElementById('machine').value = machine;
    document.getElementById('employee').value = employee;
    document.getElementById('jobs_description').value = jobsDescription;

    // Set a hidden input to specify that this is an update
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'update';
    document.querySelector('.job-description-form').appendChild(actionInput);
}

function deleteJob(jobId) {
    if (confirm("Are you sure you want to delete Job ID: " + jobId + "?")) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = ''; // Current script

        const jobIdInput = document.createElement('input');
        jobIdInput.type = 'hidden';
        jobIdInput.name = 'job_id';
        jobIdInput.value = jobId;

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';

        form.appendChild(jobIdInput);
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}
