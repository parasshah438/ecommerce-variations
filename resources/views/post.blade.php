<form id="jobForm">
    @csrf

    <input type="text" id="job_title" class="form-control" placeholder="Job Title">

    <select id="job_category" class="form-control mt-2">
        <option value="">Select Category</option>
        <option value="IT">IT</option>
        <option value="Marketing">Marketing</option>
    </select>

    <select id="job_sub_category" class="form-control mt-2">
        <option value="">Select Sub Category</option>
        <option value="PHP Developer">PHP Developer</option>
        <option value="SEO Executive">SEO Executive</option>
    </select>

    <button type="button" id="generateBtn" class="btn btn-primary mt-3">
        Generate Description
    </button>

    <textarea id="job_description" name="description"></textarea>
</form>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
    CKEDITOR.replace('job_description');

    document.getElementById('generateBtn').addEventListener('click', function () {
        fetch("{{ route('job.generate.description') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                title: document.getElementById('job_title').value,
                category: document.getElementById('job_category').value,
                sub_category: document.getElementById('job_sub_category').value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.description) {
                CKEDITOR.instances.job_description.setData(data.description);
            }
        });
    });
</script>
