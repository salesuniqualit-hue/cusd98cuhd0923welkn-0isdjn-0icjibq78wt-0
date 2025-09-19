<?php // modules/attendance/templates/index.php ?>

<div class="page-header">
    <h1>Attendance</h1>
    <div id="current-time" style="font-size: 1.5rem; font-weight: bold;"></div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php elseif (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php endif; ?>

<?php if (is_attendance_user(current_user())): ?>
<div class="card">
    <div class="card-body text-center">
        <?php
        // --- FIX IS HERE: Use the variable passed from the route, not a new function call ---
        $is_punched_in = $todays_record && !$todays_record['punch_out_time'];
        ?>

        <?php if ($is_punched_in): // User is currently punched in ?>
            <h2>You are currently punched in.</h2>
            <p><strong>Punch In Time:</strong> <?php echo e(date('h:i:s A', strtotime($todays_record['punch_in_time']))); ?></p>
            <p id="worked-hours"><strong>Hours Worked Today:</strong> <span></span></p>
            <form action="<?php echo url('/attendance/punch_out'); ?>" method="POST" onsubmit="return setLocation(this, 'out')">
                <input type="hidden" name="attendance_id" value="<?php echo e($todays_record['id']); ?>">
                <input type="hidden" name="location" id="location_out">
                <button type="submit" class="btn btn-danger btn-lg">Punch Out</button>
            </form>
        <?php else: // User is punched out or it's a new day ?>
            <h2>Ready to start your day?</h2>
            <form action="<?php echo url('/attendance/punch_in'); ?>" method="POST" onsubmit="return setLocation(this, 'in')">
                <input type="hidden" name="location" id="location_in">
                <button type="submit" class="btn btn-success btn-lg">Punch In</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (is_attendance_manager(current_user())): ?>
<div class="card mt-4">
    <div class="card-header">
        <h4>Manager Actions</h4>
    </div>
    <div class="card-body">
        <a href="<?php echo url('/attendance/report'); ?>" class="btn btn-info">View Attendance Report</a>
        <a href="<?php echo url('/attendance/holidays'); ?>" class="btn btn-secondary">Manage Holidays</a>
    </div>
</div>
<?php endif; ?>

<script>
// Live Clock
const timeEl = document.getElementById('current-time');
function updateTime() {
    const now = new Date();
    timeEl.textContent = now.toLocaleTimeString();
}
setInterval(updateTime, 1000);
updateTime();

// Worked Hours Counter
const workedHoursEl = document.getElementById('worked-hours');
<?php if (isset($todays_record) && $todays_record && !$todays_record['punch_out_time']): ?>
    const punchInTime = new Date('<?php echo e($todays_record['punch_in_time']); ?>').getTime();
    
    function updateWorkedHours() {
        const now = new Date().getTime();
        const diff = now - punchInTime;

        let hours = Math.floor(diff / (1000 * 60 * 60));
        let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((diff % (1000 * 60)) / 1000);

        hours = String(hours).padStart(2, '0');
        minutes = String(minutes).padStart(2, '0');
        seconds = String(seconds).padStart(2, '0');
        
        if (workedHoursEl) {
            workedHoursEl.querySelector('span').textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    if (workedHoursEl) {
        setInterval(updateWorkedHours, 1000);
        updateWorkedHours();
    }
<?php endif; ?>


// Geolocation with async/await for better form handling
async function setLocation(form, type) {
    const locationInput = document.getElementById(`location_${type}`);
    const button = form.querySelector('button');
    button.disabled = true;
    button.textContent = 'Getting Location...';

    try {
        const position = await new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error("Geolocation is not supported."));
            }
            navigator.geolocation.getCurrentPosition(resolve, reject);
        });

        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        const response = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=en`);
        if (!response.ok) {
            throw new Error(`API call failed with status: ${response.status}`);
        }
        const data = await response.json();
        
        const address = `${data.locality}, ${data.city}, ${data.principalSubdivision}, ${data.countryName}`;
        locationInput.value = address;
        return true; // Allows the form to submit
    } catch (error) {
        alert(`Could not get location: ${error.message}`);
        button.disabled = false;
        button.textContent = type === 'in' ? 'Punch In' : 'Punch Out';
        return false; // Prevents the form from submitting
    }
}
</script>