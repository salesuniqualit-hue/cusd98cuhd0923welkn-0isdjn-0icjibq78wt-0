<?php
// modules/sku_versions/templates/edit.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/sku_versions'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/sku_versions/' . $version['id'] . '/update'); ?>" method="POST">
             <fieldset>
                <legend>Core Information</legend>
                <div class="form-group">
                    <label for="sku_id">SKU</label>
                    <select id="sku_id" name="sku_id" class="form-control" required>
                        <option value="">-- Select SKU --</option>
                        <?php foreach ($skus as $sku): ?>
                            <option value="<?php echo e($sku['id']); ?>" <?php echo ($sku['id'] == $version['sku_id']) ? 'selected' : ''; ?>>
                                <?php echo e($sku['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="version_number">Version Number</label>
                    <input type="text" id="version_number" name="version_number" class="form-control" value="<?php echo e($version['version_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="release_date">Release Date</label>
                    <input type="date" id="release_date" name="release_date" class="form-control" value="<?php echo e($version['release_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="changelog_id">Link to Changelog</label>
                    <select id="changelog_id" name="changelog_id" class="form-control">
                        <option value="">-- No Changelog --</option>
                        <?php foreach ($changelogs as $changelog): ?>
                            <option value="<?php echo e($changelog['id']); ?>" <?php echo ($changelog['id'] == $version['changelog_id']) ? 'selected' : ''; ?>>
                                <?php echo e($changelog['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Only unlinked changelogs (and the currently linked one) are shown.</small>
                </div>
                <div class="form-group">
                    <label for="description">Description (Major Changes)</label>
                    <textarea id="description" name="description" class="form-control"><?php echo e($version['description']); ?></textarea>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>Compatibility & Links</legend>
                <div class="form-group">
                    <label for="tally_compat_from">Compatible with Tally Release (From)</label>
                    <input type="text" id="tally_compat_from" name="tally_compat_from" class="form-control" value="<?php echo e($version['tally_compat_from']); ?>">
                </div>
                <div class="form-group">
                    <label for="tally_compat_to">Compatible with Tally Release (To)</label>
                    <input type="text" id="tally_compat_to" name="tally_compat_to" class="form-control" value="<?php echo e($version['tally_compat_to']); ?>">
                </div>
                 <div class="form-group">
                    <label for="link_product">Download Product Link (URL)</label>
                    <input type="url" id="link_product" name="link_product" class="form-control" value="<?php echo e($version['link_product']); ?>">
                </div>
                <div class="form-group">
                    <label for="link_manual">Download User Manual Link (URL)</label>
                    <input type="url" id="link_manual" name="link_manual" class="form-control" value="<?php echo e($version['link_manual']); ?>">
                </div>
                <div class="form-group">
                    <label for="link_ppt">Download PPT Link (URL)</label>
                    <input type="url" id="link_ppt" name="link_ppt" class="form-control" value="<?php echo e($version['link_ppt']); ?>">
                </div>
                <div class="form-group">
                    <label for="link_faq">Download Issues/FAQs Link (URL)</label>
                    <input type="url" id="link_faq" name="link_faq" class="form-control" value="<?php echo e($version['link_faq']); ?>">
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Update Version</button>
        </form>
    </div>
</div>