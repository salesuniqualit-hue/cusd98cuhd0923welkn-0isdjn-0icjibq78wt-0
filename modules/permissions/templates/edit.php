<?php
// modules/permissions/templates/edit.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/permissions'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/permissions/store'); ?>" method="POST">
            <input type="hidden" name="user_id" value="<?php echo e($user_to_manage['id']); ?>">
            
            <table class="data-table permissions-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th class="text-center">View</th>
                        <th class="text-center">Create</th>
                        <th class="text-center">Update</th>
                        <th class="text-center">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): 
                        $perm = $current_permissions[$module['id']] ?? [];
                    ?>
                    <tr>
                        <td data-label="Module"><?php echo e($module['name']); ?></td>
                        <td data-label="View" class="text-center">
                            <input type="checkbox" name="permissions[<?php echo $module['id']; ?>][view]" value="1" <?php echo !empty($perm['can_view']) ? 'checked' : ''; ?>>
                        </td>
                        <td data-label="Create" class="text-center">
                            <input type="checkbox" name="permissions[<?php echo $module['id']; ?>][create]" value="1" <?php echo !empty($perm['can_create']) ? 'checked' : ''; ?>>
                        </td>
                        <td data-label="Update" class="text-center">
                            <input type="checkbox" name="permissions[<?php echo $module['id']; ?>][update]" value="1" <?php echo !empty($perm['can_update']) ? 'checked' : ''; ?>>
                        </td>
                        <td data-label="Delete" class="text-center">
                            <input type="checkbox" name="permissions[<?php echo $module['id']; ?>][delete]" value="1" <?php echo !empty($perm['can_delete']) ? 'checked' : ''; ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary mt-3">Save Permissions</button>
        </form>
    </div>
</div>

<style>
.permissions-table .text-center { text-align: center; }
.permissions-table input[type="checkbox"] { width: 20px; height: 20px; }
</style>