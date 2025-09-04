@extends('master.layout')

@section('content')
<div class="modern-content-wrapper">
  <!-- Dashboard Header -->
  <div class="dashboard-header">
    <h1 class="dashboard-title">Dashboard</h1>
    <div class="live-clock-card">
      <div class="clock-label">Malaysia Time</div>
      <div class="live-time" id="liveTime"></div>
      <div class="live-date" id="liveDate"></div>
    </div>
  </div>

  <!-- Real-time Stats Grid -->
  <div class="stats-grid">
    <div class="modern-stat-card primary">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="totalStudents">{{ $totalStudents }}</div>
          <div class="stat-label">Total Students</div>
          <div class="stat-trend trend-neutral">
            <i class="fas fa-users"></i> Registered
          </div>
        </div>
        <div class="stat-icon primary">
          <i class="fas fa-users"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card success">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="presentToday">{{ $present }}</div>
          <div class="stat-label">Present Today</div>
          <div class="stat-trend trend-up">
            <i class="fas fa-arrow-up"></i>
            <span id="presentPercentage">{{ $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0 }}%</span>
          </div>
        </div>
        <div class="stat-icon success">
          <i class="fas fa-user-check"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card danger">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="absentToday">{{ $absent }}</div>
          <div class="stat-label">Absent Today</div>
          <div class="stat-trend trend-down">
            <i class="fas fa-arrow-down"></i>
            <span id="absentPercentage">{{ $totalStudents > 0 ? round(($absent / $totalStudents) * 100, 1) : 0 }}%</span>
          </div>
        </div>
        <div class="stat-icon danger">
          <i class="fas fa-user-times"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card warning">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="lateCount">0</div>
          <div class="stat-label">Late Arrivals</div>
          <div class="stat-trend trend-warning">
            <i class="fas fa-clock"></i> After 9:00 AM
          </div>
        </div>
        <div class="stat-icon warning">
          <i class="fas fa-clock"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card info">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="earlyCount">0</div>
          <div class="stat-label">Early Arrivals</div>
          <div class="stat-trend trend-up">
            <i class="fas fa-arrow-up"></i> Before 8:30 AM
          </div>
        </div>
        <div class="stat-icon info">
          <i class="fas fa-user-clock"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card secondary">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="onTimeCount">0</div>
          <div class="stat-label">On Time</div>
          <div class="stat-trend trend-up">
            <i class="fas fa-check-circle"></i> 8:30-9:00 AM
          </div>
        </div>
        <div class="stat-icon secondary">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="dashboard-grid">
    <!-- Attendance by Courses -->
    <div class="chart-card course-attendance-card">
      <h3 class="card-title">
        <i class="fas fa-chart-pie"></i>
        Attendance by Courses
        <div class="refresh-btn" onclick="refreshCourseData()">
          <i class="fas fa-sync-alt" id="courseRefreshIcon"></i>
        </div>
      </h3>
      <div class="course-charts-grid" id="courseChartsGrid">
        @if($attendanceByCourse->isEmpty())
          <div class="no-data">
            <i class="fas fa-chart-pie"></i>
            <p>No course attendance data available</p>
          </div>
        @else
          @foreach($attendanceByCourse as $index => $course)
            <div class="course-pie-chart">
              <div class="course-header">
                <h4>{{ $course->course_code }}</h4>
                <span class="course-total">{{ $course->total }} present</span>
              </div>
              <div class="pie-chart-container">
                <canvas id="courseChart{{ $index }}" width="150" height="150"></canvas>
              </div>
              <div class="course-stats">
                <div class="stat-item present">
                  <span class="stat-dot present-dot"></span>
                  <span>Present: {{ $course->total }}</span>
                </div>
                <div class="stat-item absent">
                  <span class="stat-dot absent-dot"></span>
                  <span>Absent: {{ $totalStudents - $course->total }}</span>
                </div>
              </div>
            </div>
          @endforeach
        @endif
      </div>
    </div>

    <!-- Weekly Attendance Trend -->
    <div class="chart-card">
      <h3 class="card-title">
        <i class="fas fa-chart-line"></i>
        Weekly Attendance Trend
      </h3>
      <div class="chart-container">
        <canvas id="weeklyChart" height="300"></canvas>
      </div>
    </div>

    <!-- Top Performing Courses -->
    <div class="chart-card">
      <h3 class="card-title">
        <i class="fas fa-trophy"></i>
        Top Performing Courses
      </h3>
      <div class="top-courses-list" id="topCoursesList">
        <!-- Will be populated dynamically -->
      </div>
    </div>

    <!-- Attendance Heatmap -->
    <div class="chart-card">
      <h3 class="card-title">
        <i class="fas fa-calendar-alt"></i>
        Attendance Heatmap
      </h3>
      <div class="heatmap-container">
        <div class="heatmap-grid" id="attendanceHeatmap">
          <!-- Heatmap will be generated dynamically -->
        </div>
        <div class="heatmap-legend">
          <span>Less</span>
          <div class="legend-colors">
            <div class="legend-color" style="background: #ebedf0;"></div>
            <div class="legend-color" style="background: #c6e48b;"></div>
            <div class="legend-color" style="background: #7bc96f;"></div>
            <div class="legend-color" style="background: #239a3b;"></div>
            <div class="legend-color" style="background: #196127;"></div>
          </div>
          <span>More</span>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.modern-content-wrapper {
  background: #ffffff;
  min-height: 100vh;
  padding: 30px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  background: #ffffff;
  border-bottom: 1px solid #e9ecef;
  padding: 20px 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.dashboard-title {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
}

.live-clock-card {
  text-align: right;
}

.clock-label {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 5px;
}

.live-time {
  font-size: 18px;
  font-weight: 600;
  color: #2ECC71;
}

.live-date {
  font-size: 14px;
  color: #7f8c8d;
  margin-top: 2px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.modern-stat-card {
  background: rgba(255,255,255,0.95);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.modern-stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.modern-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.modern-stat-card.primary::before { background: #3498db; }
.modern-stat-card.success::before { background: #2ECC71; }
.modern-stat-card.danger::before { background: #e74c3c; }
.modern-stat-card.warning::before { background: #f39c12; }
.modern-stat-card.info::before { background: #17a2b8; }
.modern-stat-card.secondary::before { background: #6c757d; }

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 36px;
  font-weight: 800;
  color: #2c3e50;
  line-height: 1;
  margin-bottom: 5px;
  transition: all 0.3s ease;
}

.stat-label {
  font-size: 16px;
  color: #7f8c8d;
  margin-bottom: 10px;
}

.stat-trend {
  font-size: 14px;
  font-weight: 600;
}

.trend-up { color: #2ECC71; }
.trend-down { color: #e74c3c; }
.trend-warning { color: #f39c12; }
.trend-neutral { color: #6c757d; }

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.primary { background: linear-gradient(135deg, #3498db, #2980b9); }
.stat-icon.success { background: linear-gradient(135deg, #2ECC71, #27AE60); }
.stat-icon.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.stat-icon.warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
.stat-icon.info { background: linear-gradient(135deg, #17a2b8, #138496); }
.stat-icon.secondary { background: linear-gradient(135deg, #6c757d, #545b62); }

.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.chart-card {
  background: rgba(255,255,255,0.95);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.course-attendance-card {
  grid-column: 1 / -1;
}

.card-title {
  font-size: 20px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.refresh-btn {
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.refresh-btn:hover {
  background: rgba(52, 152, 219, 0.1);
  color: #3498db;
}

.course-charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
}

.course-pie-chart {
  background: rgba(248, 249, 250, 0.8);
  padding: 20px;
  border-radius: 15px;
  text-align: center;
  border: 1px solid rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.course-pie-chart:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.course-header h4 {
  font-size: 18px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 5px;
}

.course-total {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 15px;
  display: block;
}

.pie-chart-container {
  margin: 20px 0;
  display: flex;
  justify-content: center;
}

.course-stats {
  display: flex;
  justify-content: space-around;
  margin-top: 15px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
}

.stat-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.present-dot { background: #2ECC71; }
.absent-dot { background: #e74c3c; }

.chart-container {
  position: relative;
  height: 300px;
}

.heatmap-container {
  padding: 20px 0;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
  margin-bottom: 15px;
}

.heatmap-day {
  width: 35px;
  height: 35px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.heatmap-day:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.heatmap-legend {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 12px;
  color: #7f8c8d;
}

.legend-colors {
  display: flex;
  gap: 2px;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.top-courses-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.top-course-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.1));
  border-radius: 12px;
  border-left: 4px solid #3498db;
  transition: all 0.3s ease;
}

.top-course-item:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.course-rank {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

.course-details h5 {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 3px;
}

.course-details p {
  font-size: 12px;
  color: #7f8c8d;
}

.course-percentage {
  font-size: 18px;
  font-weight: 700;
  color: #2ECC71;
}

.no-data {
  text-align: center;
  color: #7f8c8d;
  padding: 40px 20px;
}

.no-data i {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.attendance-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  background: white;
  padding: 15px 20px;
  border-radius: 10px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  transform: translateX(100%);
  transition: transform 0.3s ease;
  z-index: 1000;
  border-left: 4px solid #2ECC71;
}

.attendance-notification.show {
  transform: translateX(0);
}

.attendance-notification.late {
  border-left-color: #f39c12;
}

.attendance-notification.early {
  border-left-color: #17a2b8;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: #2c3e50;
}

.notification-content i {
  color: #2ECC71;
}

@media (max-width: 1200px) {
  .course-charts-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .modern-content-wrapper {
    padding: 15px;
  }

  .course-charts-grid {
    grid-template-columns: 1fr;
  }

  .heatmap-grid {
    grid-template-columns: repeat(7, minmax(25px, 1fr));
  }

  .heatmap-day {
    width: 25px;
    height: 25px;
    font-size: 10px;
  }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let courseCharts = [];
let topCoursesChart = null;
let weeklyChart = null;

// Real-time clock
function updateTime() {
  const now = new Date();
  const timeOptions = {
    timeZone: "Asia/Kuala_Lumpur",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: true
  };
  const dateOptions = {
    timeZone: "Asia/Kuala_Lumpur",
    year: "numeric",
    month: "long",
    day: "numeric",
    weekday: "long"
  };

  const timeElement = document.getElementById("liveTime");
  const dateElement = document.getElementById("liveDate");

  if (timeElement) {
    timeElement.innerText = new Intl.DateTimeFormat("en-MY", timeOptions).format(now);
  }
  if (dateElement) {
    dateElement.innerText = "Today: " + new Intl.DateTimeFormat("en-MY", dateOptions).format(now);
  }
}

// Initialize course pie charts
function initializeCourseCharts() {
  // Clear existing charts
  courseCharts.forEach(chart => {
    if (chart) chart.destroy();
  });
  courseCharts = [];

  // Fetch real-time course data
  fetch('/admin/dashboard/course-attendance')
    .then(response => response.json())
    .then(courseData => {
      const container = document.getElementById('courseChartsGrid');
      if (!container) return;

      container.innerHTML = '';

      if (courseData.length === 0) {
        container.innerHTML = `
          <div class="no-data">
            <i class="fas fa-chart-pie"></i>
            <p>No course attendance data available</p>
          </div>
        `;
        return;
      }

      courseData.forEach((course, index) => {
        const courseDiv = document.createElement('div');
        courseDiv.className = 'course-pie-chart';
        courseDiv.innerHTML = `
          <div class="course-header">
            <h4>${course.course_code}</h4>
            <span class="course-total">${course.total}/${course.enrolled || course.total} students</span>
          </div>
          <div class="pie-chart-container">
            <canvas id="courseChart${index}" width="150" height="150"></canvas>
          </div>
          <div class="course-stats">
            <div class="stat-item present">
              <span class="stat-dot present-dot"></span>
              <span>Present: ${course.total}</span>
            </div>
            <div class="stat-item absent">
              <span class="stat-dot absent-dot"></span>
              <span>Absent: ${(course.enrolled || course.total) - course.total}</span>
            </div>
          </div>
        `;
        container.appendChild(courseDiv);

        const ctx = document.getElementById(`courseChart${index}`).getContext('2d');
        const enrolled = course.enrolled || course.total;
        const chart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Present', 'Absent'],
            datasets: [{
              data: [course.total, enrolled - course.total],
              backgroundColor: ['#2ECC71', '#ecf0f1'],
              borderWidth: 3,
              borderColor: '#ffffff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            cutout: '60%'
          }
        });

        courseCharts.push(chart);
      });
    })
    .catch(error => {
      console.error('Error loading course data:', error);
    });
}

// Initialize weekly chart
function initializeWeeklyChart() {
  const ctx = document.getElementById('weeklyChart');
  if (!ctx) return;

  // Destroy existing chart
  if (weeklyChart) {
    weeklyChart.destroy();
  }

  fetch('/admin/dashboard/weekly-attendance')
    .then(response => response.json())
    .then(data => {
      weeklyChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.map(d => d.day),
          datasets: [
            {
              label: 'Present',
              data: data.map(d => d.present),
              borderColor: '#2ECC71',
              backgroundColor: 'rgba(46, 204, 113, 0.1)',
              fill: true,
              tension: 0.4
            },
            {
              label: 'Absent',
              data: data.map(d => d.absent),
              borderColor: '#e74c3c',
              backgroundColor: 'rgba(231, 76, 60, 0.1)',
              fill: true,
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'top' }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });
    })
    .catch(error => console.error('Error loading weekly data:', error));
}

// Initialize attendance heatmap
function initializeHeatmap() {
  const container = document.getElementById('attendanceHeatmap');
  if (!container) return;

  const today = new Date();
  const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

  fetch(`/admin/dashboard/attendance-stats?start_date=${thirtyDaysAgo.toISOString().split('T')[0]}&end_date=${today.toISOString().split('T')[0]}`)
    .then(response => response.json())
    .then(data => {
      container.innerHTML = '';

      for (let i = 29; i >= 0; i--) {
        const date = new Date(today.getTime() - i * 24 * 60 * 60 * 1000);
        const dateStr = date.toISOString().split('T')[0];
        const dayData = data.find(d => d.attendance_date === dateStr);

        const dayElement = document.createElement('div');
        dayElement.className = 'heatmap-day';
        dayElement.textContent = date.getDate();
        dayElement.title = `${date.toDateString()}: ${dayData ? dayData.attendance_percentage + '% attendance' : 'No data'}`;

        let color = '#ebedf0';
        if (dayData) {
          const percentage = dayData.attendance_percentage;
          if (percentage >= 90) color = '#196127';
          else if (percentage >= 75) color = '#239a3b';
          else if (percentage >= 50) color = '#7bc96f';
          else if (percentage >= 25) color = '#c6e48b';
        }

        dayElement.style.backgroundColor = color;
        dayElement.style.color = color === '#ebedf0' ? '#666' : 'white';
        container.appendChild(dayElement);
      }
    })
    .catch(error => console.error('Error loading heatmap data:', error));
}

// Initialize top courses
function initializeTopCourses() {
  const container = document.getElementById('topCoursesList');
  if (!container) return;

  fetch('/admin/dashboard/top-courses')
    .then(response => response.json())
    .then(data => {
      container.innerHTML = '';

      if (data.length === 0) {
        container.innerHTML = `
          <div class="no-data">
            <i class="fas fa-trophy"></i>
            <p>No course performance data available</p>
          </div>
        `;
        return;
      }

      data.forEach((course, index) => {
        const courseItem = document.createElement('div');
        courseItem.className = 'top-course-item';
        courseItem.innerHTML = `
          <div class="course-rank">${index + 1}</div>
          <div class="course-details">
            <h5>${course.course_code}</h5>
            <p>${course.present_students}/${course.enrolled_students} students</p>
          </div>
          <div class="course-percentage">${course.attendance_percentage}%</div>
        `;
        container.appendChild(courseItem);
      });
    })
    .catch(error => console.error('Error loading top courses:', error));
}

// Refresh functions
function refreshDashboardData() {
  fetch('/admin/dashboard/realtime-data', {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    const elements = {
      totalStudents: document.getElementById('totalStudents'),
      presentToday: document.getElementById('presentToday'),
      absentToday: document.getElementById('absentToday'),
      presentPercentage: document.getElementById('presentPercentage'),
      absentPercentage: document.getElementById('absentPercentage')
    };

    if (elements.totalStudents) elements.totalStudents.textContent = data.totalStudents;
    if (elements.presentToday) elements.presentToday.textContent = data.present;
    if (elements.absentToday) elements.absentToday.textContent = data.absent;
    if (elements.presentPercentage) elements.presentPercentage.textContent = data.presentPercentage + '%';
    if (elements.absentPercentage) elements.absentPercentage.textContent = data.absentPercentage + '%';

    // Update time-based stats
    if (data.timeBasedStats) {
      const timeElements = {
        lateCount: document.getElementById('lateCount'),
        earlyCount: document.getElementById('earlyCount'),
        onTimeCount: document.getElementById('onTimeCount')
      };

      if (timeElements.lateCount) timeElements.lateCount.textContent = data.timeBasedStats.late;
      if (timeElements.earlyCount) timeElements.earlyCount.textContent = data.timeBasedStats.early;
      if (timeElements.onTimeCount) timeElements.onTimeCount.textContent = data.timeBasedStats.onTime;
    }
  })
  .catch(error => console.error('Error refreshing data:', error));
}

function refreshCourseData() {
  const refreshIcon = document.getElementById('courseRefreshIcon');
  if (refreshIcon) refreshIcon.classList.add('spinning');

  initializeCourseCharts();
  initializeTopCourses();

  setTimeout(() => {
    if (refreshIcon) refreshIcon.classList.remove('spinning');
  }, 2000);
}

// Animation helper
function animateValue(element, start, end, duration) {
  if (!element || start === end) return;

  const range = end - start;
  const stepTime = Math.abs(Math.floor(duration / range));
  const timer = stepTime < 50 ? 50 : stepTime;
  const startTime = new Date().getTime();
  const endTime = startTime + duration;

  function run() {
    const now = new Date().getTime();
    const remaining = Math.max((endTime - now) / duration, 0);
    const value = Math.round(end - (remaining * range));
    element.textContent = value;
    if (value !== end) {
      setTimeout(run, timer);
    }
  }
  run();
}

// Notification system
function showAttendanceNotification(studentName, status) {
  const notification = document.createElement('div');
  notification.className = `attendance-notification ${status.toLowerCase()}`;
  notification.innerHTML = `
    <div class="notification-content">
      <i class="fas fa-user-check"></i>
      <span><strong>${studentName}</strong> marked ${status}</span>
    </div>
  `;

  document.body.appendChild(notification);

  setTimeout(() => notification.classList.add('show'), 100);

  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      if (document.body.contains(notification)) {
        document.body.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Start clock immediately
  updateTime();
  setInterval(updateTime, 1000);

  // Initialize charts with delays to prevent conflicts
  setTimeout(initializeCourseCharts, 500);
  setTimeout(initializeWeeklyChart, 1000);
  setTimeout(initializeHeatmap, 1500);
  setTimeout(initializeTopCourses, 2000);

  // Set up auto-refresh intervals
  setInterval(refreshDashboardData, 30000); // Every 30 seconds
  setInterval(() => {
    initializeHeatmap();
    initializeTopCourses();
  }, 60000); // Every minute
  setInterval(initializeCourseCharts, 45000); // Every 45 seconds for courses
});

// Handle window resize
window.addEventListener('resize', function() {
  [topCoursesChart, weeklyChart, ...courseCharts].forEach(chart => {
    if (chart) chart.resize();
  });
});

// Initialize course charts on page load with existing data
function initializeInitialCourseCharts() {
  const courseData = @json($attendanceByCourse);
  const totalStudents = {{ $totalStudents }};

  courseData.forEach((course, index) => {
    const ctx = document.getElementById(`courseChart${index}`);
    if (ctx) {
      const presentCount = course.total;
      const absentCount = totalStudents - presentCount;

      const chart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels: ['Present', 'Absent'],
          datasets: [{
            data: [presentCount, absentCount],
            backgroundColor: ['#2ECC71', '#e74c3c'],
            borderWidth: 2,
            borderColor: '#ffffff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          cutout: '60%'
        }
      });

      courseCharts.push(chart);
    }
  });
}

// Initialize initial charts if data exists
if (@json($attendanceByCourse).length > 0) {
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initializeInitialCourseCharts, 100);
  });
}
</script>

@endsection


