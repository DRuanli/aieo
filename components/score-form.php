<div class="card">
    <div class="card-header">
        <h5>Add New Score</h5>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="listening" class="form-label">Listening (0-9)</label>
                <input type="number" class="form-control" id="listening" name="listening" min="0" max="9" step="0.5" required>
            </div>
            <div class="mb-3">
                <label for="reading" class="form-label">Reading (0-9)</label>
                <input type="number" class="form-control" id="reading" name="reading" min="0" max="9" step="0.5" required>
            </div>
            <div class="mb-3">
                <label for="writing" class="form-label">Writing (0-9)</label>
                <input type="number" class="form-control" id="writing" name="writing" min="0" max="9" step="0.5" required>
            </div>
            <div class="mb-3">
                <label for="speaking" class="form-label">Speaking (0-9)</label>
                <input type="number" class="form-control" id="speaking" name="speaking" min="0" max="9" step="0.5" required>
            </div>
            <div class="mb-3">
                <label for="overall" class="form-label">Overall (0-9)</label>
                <input type="number" class="form-control" id="overall" name="overall" min="0" max="9" step="0.5" required>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
            <button type="submit" name="add_score" class="btn btn-primary">Add Score</button>
        </form>
    </div>
</div>