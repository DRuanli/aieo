<?php
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Vocabulary List</h3>
                </div>
                <div class="card-body">
                    <?php
                    $vocabulary = getVocabulary();
                    if (empty($vocabulary)) {
                        echo '<div class="alert alert-info">No vocabulary items found. Please add some!</div>';
                    } else {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>English</th>
                                    <th>Vietnamese</th>
                                    <th>Context</th>
                                    <th>Synonyms</th>
                                    <th>Antonyms</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vocabulary as $index => $item) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['english']); ?></td>
                                    <td><?php echo htmlspecialchars($item['vietnamese']); ?></td>
                                    <td><?php echo htmlspecialchars($item['context']); ?></td>
                                    <td><?php echo htmlspecialchars(implode(', ', $item['synonyms'])); ?></td>
                                    <td><?php echo htmlspecialchars(implode(', ', $item['antonyms'])); ?></td>
                                    <td>
                                        <a href="add.php?edit=<?php echo $index; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="index.php?delete=<?php echo $index; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>