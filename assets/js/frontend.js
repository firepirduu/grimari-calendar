jQuery(document).ready(function ($) {
  let currentDate = new Date();
  let selectedService = null;

  // Initialize calendar
  renderCalendar();
  updateMonthDisplay();

  // Event listeners
  $("#prev-month").on("click", function () {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
    updateMonthDisplay();
  });

  $("#next-month").on("click", function () {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
    updateMonthDisplay();
  });

  $("#today-btn").on("click", function () {
    currentDate = new Date();
    renderCalendar();
    updateMonthDisplay();
  });

  // View toggle
  $(".view-btn").on("click", function () {
    $(".view-btn").removeClass("active");
    $(this).addClass("active");
    // View switching logic would go here
  });

  // Modal controls
  $("#close-modal, #cancel-booking").on("click", function () {
    closeBookingModal();
  });

  // Form submission
  $("#booking-form").on("submit", function (e) {
    e.preventDefault();
    submitBookingApplication();
  });

  function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Get calendar data
    loadCalendarData(year, month + 1);
  }

  function loadCalendarData(year, month) {
    $.ajax({
      url: plandok_frontend.ajax_url,
      type: "POST",
      data: {
        action: "plandok_get_available_slots",
        nonce: plandok_frontend.nonce,
        year: year,
        month: month,
      },
      success: function (response) {
        if (response.success) {
          renderCalendarGrid(year, month - 1, response.data);
        }
      },
    });
  }

  function renderCalendarGrid(year, month, availableSlots) {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const firstDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();

    let calendarHTML = '<div class="calendar-header-row">';
    const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    dayNames.forEach((day) => {
      calendarHTML += `<div class="day-header">${day}</div>`;
    });
    calendarHTML += '</div><div class="calendar-body">';

    // Previous month days
    const prevMonth = month === 0 ? 11 : month - 1;
    const prevYear = month === 0 ? year - 1 : year;
    const daysInPrevMonth = new Date(prevYear, prevMonth + 1, 0).getDate();

    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
      const day = daysInPrevMonth - i;
      calendarHTML += `<div class="calendar-day other-month">${day}</div>`;
    }

    // Current month days
    for (let day = 1; day <= daysInMonth; day++) {
      const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
      const isToday = dateStr === new Date().toISOString().split("T")[0];
      const daySlots = getAvailableSlotsForDate(dateStr);

      let dayClass = "calendar-day";
      if (isToday) dayClass += " today";

      calendarHTML += `<div class="${dayClass}" data-date="${dateStr}">`;
      calendarHTML += `<div class="day-number">${day}</div>`;

      if (daySlots.length > 0) {
        calendarHTML += '<div class="available-slots">';
        daySlots.slice(0, 3).forEach((slot) => {
          calendarHTML += `<div class="slot-item" data-service='${JSON.stringify(slot)}'>`;
          calendarHTML += `<div class="slot-time">${slot.time}</div>`;
          calendarHTML += `<div class="slot-service">${slot.service_name}</div>`;
          calendarHTML += "</div>";
        });

        if (daySlots.length > 3) {
          calendarHTML += `<div class="more-slots">+${daySlots.length - 3} more</div>`;
        }
        calendarHTML += "</div>";
      }

      calendarHTML += "</div>";
    }

    // Next month days
    const totalCells = Math.ceil((firstDayOfWeek + daysInMonth) / 7) * 7;
    const remainingCells = totalCells - (firstDayOfWeek + daysInMonth);

    for (let day = 1; day <= remainingCells; day++) {
      calendarHTML += `<div class="calendar-day other-month">${day}</div>`;
    }

    calendarHTML += "</div>";

    $("#calendar-grid").html(calendarHTML);

    // Add click handlers for slots
    $(".slot-item").on("click", function () {
      const serviceData = $(this).data("service");
      showServiceDetails(serviceData);
    });
  }

  function getAvailableSlotsForDate(date) {
    // This would be populated by actual AJAX call in a real implementation
    // For now, return sample data
    const sampleSlots = [
      {
        time: "09:00",
        service_name: "Man's Haircut",
        duration: "30min",
        price: 10,
      },
      {
        time: "10:30",
        service_name: "Women's Cut & Style",
        duration: "1h 30min",
        price: 45,
      },
      {
        time: "14:00",
        service_name: "Hair Coloring",
        duration: "2h",
        price: 80,
      },
    ];

    return sampleSlots;
  }

  function showServiceDetails(serviceData) {
    selectedService = serviceData;

    const detailsHTML = `
            <h3>Service Details</h3>
            <div class="service-info">
                <h4>${serviceData.service_name}</h4>
                <div class="service-meta">
                    <span class="duration">🕒 ${serviceData.time} (${serviceData.duration})</span>
                    <span class="price">💰 €${serviceData.price}</span>
                </div>
            </div>
            <div class="service-actions">
                <button class="apply-btn" onclick="openBookingModal()">Apply for This Appointment</button>
            </div>
        `;

    $("#service-details").html(detailsHTML).show();
    $("#default-message").hide();
  }

  function updateMonthDisplay() {
    const monthNames = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ];

    const monthName = monthNames[currentDate.getMonth()];
    const year = currentDate.getFullYear();

    $("#current-month").text(`${monthName} ${year}`);
  }

  window.openBookingModal = function () {
    if (!selectedService) return;

    const serviceInfoHTML = `
            <h4>${selectedService.service_name}</h4>
            <div class="service-details-modal">
                <span>🕒 ${selectedService.time} (${selectedService.duration})</span>
                <span>💰 €${selectedService.price}</span>
            </div>
        `;

    $("#selected-service-info").html(serviceInfoHTML);
    $("#booking-modal").show();
  };

  function closeBookingModal() {
    $("#booking-modal").hide();
    $("#booking-form")[0].reset();
  }

  function submitBookingApplication() {
    const formData = {
      action: "plandok_submit_application",
      nonce: plandok_frontend.nonce,
      service_id: selectedService.service_id || 1,
      first_name: $("#first_name").val(),
      last_name: $("#last_name").val(),
      phone: $("#phone").val(),
      email: $("#email").val(),
      notes: $("#notes").val(),
      preferred_date:
        selectedService.date || new Date().toISOString().split("T")[0],
      preferred_time: selectedService.time || "09:00",
    };

    $.ajax({
      url: plandok_frontend.ajax_url,
      type: "POST",
      data: formData,
      success: function (response) {
        if (response.success) {
          alert(
            "Booking application submitted successfully! We will contact you soon.",
          );
          closeBookingModal();
        } else {
          alert("Error: " + response.data);
        }
      },
      error: function () {
        alert("An error occurred. Please try again.");
      },
    });
  }
});
