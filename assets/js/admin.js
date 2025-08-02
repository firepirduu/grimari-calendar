jQuery(document).ready(function ($) {
  // Global variables
  let currentDate = new Date();

  // Initialize admin functionality
  initCalendar();
  initForms();
  initDataTables();

  // Calendar functionality
  function initCalendar() {
    // Calendar navigation
    $(".calendar-nav-prev").on("click", function () {
      currentDate.setMonth(currentDate.getMonth() - 1);
      loadCalendarEvents();
    });

    $(".calendar-nav-next").on("click", function () {
      currentDate.setMonth(currentDate.getMonth() + 1);
      loadCalendarEvents();
    });

    $(".calendar-today").on("click", function () {
      currentDate = new Date();
      loadCalendarEvents();
    });

    // Load initial calendar
    loadCalendarEvents();
  }

  function loadCalendarEvents() {
    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data: {
        action: "plandok_get_calendar_events",
        nonce: plandok_admin.nonce,
        year: currentDate.getFullYear(),
        month: currentDate.getMonth() + 1,
      },
      success: function (response) {
        if (response.success) {
          renderCalendar(response.data);
        }
      },
    });
  }

  function renderCalendar(events) {
    // Calendar rendering logic would go here
    console.log("Calendar events loaded:", events);
  }

  // Form handling
  function initForms() {
    // Service form
    $("#service-form").on("submit", function (e) {
      e.preventDefault();
      saveService();
    });

    // Staff form
    $("#staff-form").on("submit", function (e) {
      e.preventDefault();
      saveStaff();
    });

    // Appointment form
    $("#appointment-form").on("submit", function (e) {
      e.preventDefault();
      saveAppointment();
    });

    // Delete buttons
    $(".delete-btn").on("click", function (e) {
      e.preventDefault();

      if (confirm(plandok_admin.strings.confirm_delete)) {
        const type = $(this).data("type");
        const id = $(this).data("id");
        deleteItem(type, id);
      }
    });

    // Application status updates
    $(".application-status").on("change", function () {
      const applicationId = $(this).data("id");
      const status = $(this).val();
      updateApplicationStatus(applicationId, status);
    });
  }

  function saveService() {
    const formData = $("#service-form").serialize();

    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data:
        formData + "&action=plandok_save_service&nonce=" + plandok_admin.nonce,
      success: function (response) {
        if (response.success) {
          showNotice(plandok_admin.strings.success, "success");
          location.reload();
        } else {
          showNotice(response.data || plandok_admin.strings.error, "error");
        }
      },
      error: function () {
        showNotice(plandok_admin.strings.error, "error");
      },
    });
  }

  function saveStaff() {
    const formData = $("#staff-form").serialize();

    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data:
        formData + "&action=plandok_save_staff&nonce=" + plandok_admin.nonce,
      success: function (response) {
        if (response.success) {
          showNotice(plandok_admin.strings.success, "success");
          location.reload();
        } else {
          showNotice(response.data || plandok_admin.strings.error, "error");
        }
      },
      error: function () {
        showNotice(plandok_admin.strings.error, "error");
      },
    });
  }

  function saveAppointment() {
    const formData = $("#appointment-form").serialize();

    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data:
        formData +
        "&action=plandok_save_appointment&nonce=" +
        plandok_admin.nonce,
      success: function (response) {
        if (response.success) {
          showNotice(plandok_admin.strings.success, "success");
          location.reload();
        } else {
          showNotice(response.data || plandok_admin.strings.error, "error");
        }
      },
      error: function () {
        showNotice(plandok_admin.strings.error, "error");
      },
    });
  }

  function deleteItem(type, id) {
    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data: {
        action: "plandok_delete_" + type,
        nonce: plandok_admin.nonce,
        id: id,
      },
      success: function (response) {
        if (response.success) {
          showNotice(plandok_admin.strings.success, "success");
          $('[data-id="' + id + '"]')
            .closest("tr")
            .fadeOut();
        } else {
          showNotice(response.data || plandok_admin.strings.error, "error");
        }
      },
      error: function () {
        showNotice(plandok_admin.strings.error, "error");
      },
    });
  }

  function updateApplicationStatus(applicationId, status) {
    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data: {
        action: "plandok_update_application_status",
        nonce: plandok_admin.nonce,
        application_id: applicationId,
        status: status,
      },
      success: function (response) {
        if (response.success) {
          showNotice(plandok_admin.strings.success, "success");
        } else {
          showNotice(response.data || plandok_admin.strings.error, "error");
        }
      },
      error: function () {
        showNotice(plandok_admin.strings.error, "error");
      },
    });
  }

  // Data tables initialization
  function initDataTables() {
    if ($.fn.DataTable) {
      $(".plandok-data-table").DataTable({
        pageLength: 25,
        responsive: true,
        order: [[0, "desc"]],
        language: {
          search: "Search:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          paginate: {
            first: "First",
            last: "Last",
            next: "Next",
            previous: "Previous",
          },
        },
      });
    }
  }

  // Utility functions
  function showNotice(message, type) {
    const noticeClass = type === "error" ? "notice-error" : "notice-success";
    const notice = $(
      '<div class="notice ' +
        noticeClass +
        ' is-dismissible"><p>' +
        message +
        "</p></div>",
    );

    $(".wp-header-end").after(notice);

    // Auto-dismiss after 5 seconds
    setTimeout(function () {
      notice.fadeOut();
    }, 5000);
  }

  // Color picker initialization
  if ($.fn.wpColorPicker) {
    $(".color-picker").wpColorPicker();
  }

  // Sortable tables
  if ($.fn.sortable) {
    $(".sortable-table tbody").sortable({
      handle: ".sort-handle",
      update: function (event, ui) {
        // Handle sort order update
        const order = $(this).sortable("toArray", { attribute: "data-id" });
        updateSortOrder(order);
      },
    });
  }

  function updateSortOrder(order) {
    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data: {
        action: "plandok_update_sort_order",
        nonce: plandok_admin.nonce,
        order: order,
      },
      success: function (response) {
        if (response.success) {
          showNotice("Order updated successfully", "success");
        }
      },
    });
  }

  // Time picker functionality
  $(".time-picker").on("focus", function () {
    // Simple time picker implementation
    $(this).attr("type", "time");
  });

  // Duration calculator
  $(".duration-input").on("change", function () {
    calculateEndTime();
  });

  function calculateEndTime() {
    const startTime = $("#start_time").val();
    const duration = $("#duration").val();

    if (startTime && duration) {
      // Parse duration (e.g., "1h 30min" or "30min")
      const durationMatch = duration.match(/(?:(\d+)h\s*)?(?:(\d+)min)?/);
      if (durationMatch) {
        const hours = parseInt(durationMatch[1] || 0);
        const minutes = parseInt(durationMatch[2] || 0);

        const start = new Date("2000-01-01 " + startTime);
        start.setHours(start.getHours() + hours);
        start.setMinutes(start.getMinutes() + minutes);

        const endTime = start.toTimeString().substr(0, 5);
        $("#end_time").val(endTime);
      }
    }
  }

  // Working hours toggle
  $(".working-hours-toggle").on("change", function () {
    const isEnabled = $(this).is(":checked");
    $(".working-hours-fields").toggle(isEnabled);
  });

  // Bulk actions
  $("#bulk-action-apply").on("click", function () {
    const action = $("#bulk-action-select").val();
    const selected = $(".bulk-select:checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    if (action && selected.length > 0) {
      if (
        confirm(
          'Apply "' + action + '" to ' + selected.length + " selected items?",
        )
      ) {
        applyBulkAction(action, selected);
      }
    }
  });

  function applyBulkAction(action, items) {
    $.ajax({
      url: plandok_admin.ajax_url,
      type: "POST",
      data: {
        action: "plandok_bulk_action",
        nonce: plandok_admin.nonce,
        bulk_action: action,
        items: items,
      },
      success: function (response) {
        if (response.success) {
          showNotice(response.data.message, "success");
          location.reload();
        } else {
          showNotice(response.data || plandok_admin.strings.error, "error");
        }
      },
      error: function () {
        showNotice(plandok_admin.strings.error, "error");
      },
    });
  }

  // Select all checkbox
  $("#select-all").on("change", function () {
    $(".bulk-select").prop("checked", $(this).is(":checked"));
  });
});
