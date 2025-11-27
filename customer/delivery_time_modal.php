<?php
// customer/includes/delivery_time_modal.php
?>
<div class="modal fade" id="timeModalOverlay" tabindex="-1" aria-labelledby="timeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="timeModalLabel">
          <i class="bi bi-clock-history me-2"></i>Select a Time
        </h5>
        <button type="button" class="btn-close" id="timeCloseBtn" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="mb-3">
          <label for="timeDate" class="form-label">Date</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
            <input type="date" id="timeDate" class="form-control" />
          </div>
        </div>

        <div class="mb-3">
          <label for="timeTime" class="form-label">Time</label>
          <div class="input-group">
             <span class="input-group-text"><i class="bi bi-alarm"></i></span>
            <input type="time" id="timeTime" class="form-control" step="300"/>
          </div>
        </div>
        
        <div class="alert alert-danger" id="timeError" style="display:none;" role="alert"></div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
          <button type="button" class="btn btn-sm btn-outline-secondary time-chip" data-mins="30">+30 min</button>
          <button type="button" class="btn btn-sm btn-outline-secondary time-chip" data-mins="60">+1 hr</button>
          <small id="timeNote" class="text-muted ms-auto">Lead time: <b>15 min</b></small>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="timeConfirmBtn" style="color:black; font-weight:600;">Use this time</button>
      </div>
    </div>
  </div>
</div>