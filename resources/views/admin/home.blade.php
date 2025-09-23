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
            <div class="stat-value" id="activeCourses">0</div>
            <div class="stat-label">Active Courses</div>
            <div class="stat-trend trend-up">
              <i class="fas fa-graduation-cap"></i> Currently Running
            </div>
          </div>
          <div class="stat-icon secondary">
            <i class="fas fa-graduation-cap"></i>
          </div>
        </div>
      </div>
  </div>

  <!-- Charts Section -->
  <div class="dashboard-grid">
    <!-- Course Enrollment Bar Chart -->
    <div class="chart-card enrollment-chart-card">
      <h3 class="card-title">
        <i class="fas fa-chart-bar"></i>
        Total Enrollments by Course
        <div class="refresh-btn" onclick="refreshEnrollmentChart()">
          <i class="fas fa-sync-alt" id="enrollmentRefreshIcon"></i>
        </div>
      </h3>
      <div class="chart-container">
        <canvas id="enrollmentChart" height="300"></canvas>
      </div>
      <div class="enrollment-stats" id="enrollmentStats">
        <!-- Stats will be populated dynamically -->
      </div>
    </div>

    <!-- Attendance by Courses -->
    <div class="chart-card course-attendance-card">
      <h3 class="card-title">
        <i class="fas fa-chart-pie"></i>
        Today's Attendance by Course
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
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  margin-bottom: 30px;
}

.chart-card {
  background: rgba(255,255,255,0.95);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
}

.chart-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.enrollment-chart-card {
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  border-radius: 20px;
  padding: 30px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
}

.enrollment-chart-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.enrollment-chart-card .card-title {
  font-size: 22px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.enrollment-chart-card .card-title i {
  color: #6c757d;
  margin-right: 12px;
  font-size: 20px;
}

.course-attendance-card {
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  border-radius: 20px;
  padding: 30px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
}

.course-attendance-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
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
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: transparent;
  border: none;
}

.refresh-btn:hover {
  background: rgba(108, 117, 125, 0.1);
  color: #495057;
}

.chart-container {
  position: relative;
  height: 400px;
  margin-bottom: 25px;
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(0,0,0,0.08);
}

.chart-container canvas {
  border-radius: 8px;
}

.enrollment-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 15px;
  margin-top: 25px;
  padding-top: 25px;
  border-top: 1px solid rgba(0,0,0,0.08);
}

.enrollment-stat-item {
  text-align: center;
  padding: 16px 12px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.06);
  transition: all 0.3s ease;
  position: relative;
}

.enrollment-stat-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border-color: rgba(0,0,0,0.12);
}

.enrollment-stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
  display: block;
  margin-bottom: 6px;
  transition: all 0.3s ease;
}

.enrollment-stat-label {
  font-size: 13px;
  color: #6c757d;
  font-weight: 500;
  line-height: 1.3;
}

.enrollment-stat-item:last-child {
  grid-column: 1 / -1;
  background: linear-gradient(135deg, rgba(108, 117, 125, 0.05), rgba(73, 80, 87, 0.05));
  border-color: rgba(108, 117, 125, 0.15);
}

.enrollment-stat-item:last-child .enrollment-stat-label {
  font-size: 14px;
  color: #495057;
  font-weight: 600;
}

.enrollment-loading {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.enrollment-loading i {
  font-size: 48px;
  margin-bottom: 15px;
  animation: spin 2s linear infinite;
}

.enrollment-error {
  text-align: center;
  padding: 40px 20px;
  color: #dc3545;
  background: rgba(220, 53, 69, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(220, 53, 69, 0.15);
}

.enrollment-error i {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.7;
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

@media (max-width: 1200px) {
  .course-charts-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }

  .enrollment-stats {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
  }

  .enrollment-stat-number {
    font-size: 20px;
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

  .enrollment-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  .enrollment-stat-item {
    padding: 12px 8px;
  }

  .enrollment-stat-number {
    font-size: 18px;
  }

  .enrollment-stat-label {
    font-size: 12px;
  }

  .heatmap-grid {
    grid-template-columns: repeat(7, minmax(25px, 1fr));
  }

  .heatmap-day {
    width: 25px;
    height: 25px;
    font-size: 10px;
  }

  .chart-container {
    height: 300px;
    padding: 15px;
  }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let courseCharts = [];
let enrollmentChart = null;
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

// Initialize course enrollment bar chart
function initializeEnrollmentChart() {
  console.log('Initializing enrollment chart...');

  const chartContainer = document.querySelector('.enrollment-chart-card .chart-container');
  const statsContainer = document.getElementById('enrollmentStats');

  // Show loading state
  if (chartContainer) {
    chartContainer.innerHTML = `
      <div class="enrollment-loading">
        <i class="fas fa-spinner"></i>
        <p>Loading enrollment data...</p>
      </div>
    `;
  }

  if (statsContainer) {
    statsContainer.innerHTML = `
      <div class="enrollment-stat-item" style="grid-column: 1 / -1;">
        <div class="enrollment-loading">
          <i class="fas fa-spinner" style="font-size: 24px;"></i>
          <span>Loading statistics...</span>
        </div>
      </div>
    `;
  }

  // Destroy existing chart
  if (enrollmentChart) {
    enrollmentChart.destroy();
    enrollmentChart = null;
  }

  fetch('/admin/dashboard/course-enrollment', {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    console.log('Enrollment data received:', data);

    // Restore chart container
    if (chartContainer) {
      chartContainer.innerHTML = '<canvas id="enrollmentChart" height="300"></canvas>';
    }

    const ctx = document.getElementById('enrollmentChart');
    if (!ctx) {
      console.error('Enrollment chart canvas not found');
      return;
    }

    if (!data || data.length === 0) {
      ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);

      if (chartContainer) {
        chartContainer.innerHTML = `
          <div class="no-data">
            <i class="fas fa-chart-bar"></i>
            <p>No enrollment data available</p>
            <small>Make sure you have courses and enrollments set up</small>
          </div>
        `;
      }

      if (statsContainer) {
        statsContainer.innerHTML = `
          <div class="enrollment-stat-item" style="grid-column: 1 / -1;">
            <span class="enrollment-stat-label">No enrollment data available</span>
          </div>
        `;
      }
      return;
    }

    // Sort data by enrollment count (descending)
    const sortedData = data.sort((a, b) => b.enrolled - a.enrolled);
    const maxEnrollment = Math.max(...sortedData.map(course => course.enrolled));
      const minEnrollment = Math.min(...sortedData.map(course => course.enrolled));

// If all values are the same or the range is too small, we'll adjust the chart scale differently
const needsScaleFix = (maxEnrollment - minEnrollment) <= 1;


    // Enhanced gradient colors
    const gradientColors = [
      { bg: 'rgba(52, 152, 219, 0.8)', border: '#3498db' },
      { bg: 'rgba(46, 204, 113, 0.8)', border: '#2ECC71' },
      { bg: 'rgba(231, 76, 60, 0.8)', border: '#e74c3c' },
      { bg: 'rgba(243, 156, 18, 0.8)', border: '#f39c12' },
      { bg: 'rgba(155, 89, 182, 0.8)', border: '#9b59b6' },
      { bg: 'rgba(26, 188, 156, 0.8)', border: '#1abc9c' },
      { bg: 'rgba(230, 126, 34, 0.8)', border: '#e67e22' },
      { bg: 'rgba(52, 73, 94, 0.8)', border: '#34495e' }
    ];

    enrollmentChart = new Chart(ctx.getContext('2d'), {
  type: 'bar',
  data: {
    labels: sortedData.map(course => course.course_code),
    datasets: [{
      label: 'Total Enrollments',
      data: sortedData.map(course => course.enrolled),
      backgroundColor: sortedData.map((_, index) => gradientColors[index % gradientColors.length].bg),
      borderColor: sortedData.map((_, index) => gradientColors[index % gradientColors.length].border),
      borderWidth: 2,
      borderRadius: 10,
      borderSkipped: false,
      hoverBackgroundColor: sortedData.map((_, index) => gradientColors[index % gradientColors.length].border + '90'),
      hoverBorderWidth: 3,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          font: {
            size: 14,
            weight: '600'
          },
          color: '#2c3e50',
          usePointStyle: true,
          pointStyle: 'rectRounded'
        }
      },
      tooltip: {
        backgroundColor: 'rgba(44, 62, 80, 0.95)',
        titleColor: '#ffffff',
        bodyColor: '#ffffff',
        borderColor: '#3498db',
        borderWidth: 2,
        cornerRadius: 12,
        displayColors: true,
        titleFont: {
          size: 16,
          weight: '600'
        },
        bodyFont: {
          size: 14,
          weight: '400'
        },
        padding: 15,
        callbacks: {
          title: function(context) {
            const course = sortedData[context[0].dataIndex];
            return course.title || course.course_code;
          },
          label: function(context) {
            const course = sortedData[context.dataIndex];
            return [
              `Enrollments: ${context.parsed.y}`,
              `Present Today: ${course.total || 0}`,
              `Attendance Rate: ${course.attendance_percentage || 0}%`
            ];
          },
          afterLabel: function(context) {
            const course = sortedData[context.dataIndex];
            const rank = context.dataIndex + 1;
            return `Rank: #${rank}`;
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        // FIX 1: Set explicit min/max values to prevent auto-scaling issues
        min: 0,
        max: Math.max(...sortedData.map(course => course.enrolled)) + 0,

        // FIX 2: Add grace percentage for padding
        grace: '15%',
        grid: {
          color: 'rgba(0,0,0,0.08)',
          drawBorder: false,
          lineWidth: 1
        },
        ticks: {
          color: '#7f8c8d',
          font: {
            size: 12,
            weight: '500'
          },
          padding: 10,
          // FIX 3: Force integer steps for small values
          stepSize: 1,
          callback: function(value) {
            return Math.floor(value) === value ? value : '';
          }
        },
        title: {
          display: true,
          text: 'Number of Students',
          color: '#2c3e50',
          font: {
            size: 14,
            weight: '600'
          },
          padding: 20
        }

      },
      x: {
        grid: {
          display: false
        },
        ticks: {
          color: '#7f8c8d',
          font: {
            size: 12,
            weight: '500'
          },
          maxRotation: 45,
          padding: 10
        },
        title: {
          display: true,
          text: 'Courses',
          color: '#2c3e50',
          font: {
            size: 14,
            weight: '600'
          },
          padding: 20
        }
      }
    },
    animation: {
      duration: 2000,
      easing: 'easeOutBounce'
    },
    interaction: {
      intersect: false,
      mode: 'index'
    }
  }
});

// FIX 4: Add this debug logging after chart creation
console.log('Chart data for debugging:', {
  labels: sortedData.map(course => course.course_code),
  data: sortedData.map(course => course.enrolled),
  maxValue: Math.max(...sortedData.map(course => course.enrolled)),
  chartConfig: enrollmentChart.config
});

    // Update enrollment stats with enhanced styling
    updateEnrollmentStats(sortedData);

    console.log('Enrollment chart created successfully');
  })
  .catch(error => {
    console.error('Error loading enrollment data:', error);

    // Show error state in chart container
    if (chartContainer) {
      chartContainer.innerHTML = `
        <div class="enrollment-error">
          <i class="fas fa-exclamation-triangle"></i>
          <p><strong>Error loading enrollment data</strong></p>
          <small>Please check your network connection and try again</small>
          <button onclick="initializeEnrollmentChart()"
                  style="margin-top: 15px; padding: 8px 16px; border: none; border-radius: 8px;
                         background: #e74c3c; color: white; cursor: pointer; font-weight: 600;">
            Retry
          </button>
        </div>
      `;
    }

    // Show error state in stats
    if (statsContainer) {
      statsContainer.innerHTML = `
        <div class="enrollment-stat-item enrollment-error" style="grid-column: 1 / -1;">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Error loading enrollment statistics</span>
        </div>
      `;
    }
  });
}

// Enhanced enrollment statistics update function
function updateEnrollmentStats(data) {
  const statsContainer = document.getElementById('enrollmentStats');
  if (!statsContainer || !data || data.length === 0) return;

  const totalEnrollments = data.reduce((sum, course) => sum + course.enrolled, 0);
  const avgEnrollment = Math.round(totalEnrollments / data.length);
  const maxEnrollment = Math.max(...data.map(course => course.enrolled));
  const minEnrollment = Math.min(...data.map(course => course.enrolled));
  const mostPopularCourse = data.find(course => course.enrolled === maxEnrollment);

  statsContainer.innerHTML = `

    <div class="enrollment-stat-item">
      <span class="enrollment-stat-label">
        Most Popular: <strong>${mostPopularCourse?.course_code}</strong>
        (${maxEnrollment.toLocaleString()} students)
      </span>
    </div>
  `;
}
function updateActiveCourses(courseData = null) {
  const activeCoursesElement = document.getElementById('activeCourses');
  if (!activeCoursesElement) return;

  // If course data is provided, use it directly
  if (courseData && Array.isArray(courseData)) {
    activeCoursesElement.textContent = courseData.length;
    return;
  }

  // Otherwise, try to get from existing enrollment chart
  if (enrollmentChart && enrollmentChart.data && enrollmentChart.data.labels) {
    activeCoursesElement.textContent = enrollmentChart.data.labels.length;
    return;
  }

  // Fallback: fetch from the same endpoint as enrollment chart
  fetch('/admin/dashboard/course-enrollment', {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    const courseCount = Array.isArray(data) ? data.length : 0;
    activeCoursesElement.textContent = courseCount;
  })
  .catch(error => {
    console.error('Error fetching active courses:', error);
    activeCoursesElement.textContent = 0;
  });
}


// Initialize course pie charts
function initializeCourseCharts() {
  console.log('Initializing course charts...');

  // Clear existing charts
  courseCharts.forEach(chart => {
    if (chart) chart.destroy();
  });
  courseCharts = [];

  const container = document.getElementById('courseChartsGrid');
  if (!container) {
    console.error('Course charts container not found');
    return;
  }

  // Show loading state
  container.innerHTML = `
    <div class="no-data" style="grid-column: 1 / -1;">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Loading course attendance data...</p>
    </div>
  `;

  // Fetch real-time course data
  fetch('/admin/dashboard/course-attendance', {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => {
    console.log('Course attendance response status:', response.status);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(courseData => {
    console.log('Course attendance data received:', courseData);

    // Clear loading state
    container.innerHTML = '';

    if (!courseData || courseData.length === 0) {
      container.innerHTML = `
        <div class="no-data" style="grid-column: 1 / -1;">
          <i class="fas fa-chart-pie"></i>
          <p>No course attendance data available</p>
          <small>Make sure you have courses with enrolled students</small>
        </div>
      `;
      return;
    }

    // Update active courses count using this data
    updateActiveCourses(courseData);

    // Create charts for each course
    courseData.forEach((course, index) => {
      console.log(`Creating chart for course: ${course.course_code}`, course);

      const courseDiv = document.createElement('div');
      courseDiv.className = 'course-pie-chart';

      const enrolledCount = parseInt(course.enrolled) || 0;
      const presentCount = parseInt(course.total) || 0;
      const absentCount = Math.max(0, enrolledCount - presentCount);
      const attendancePercentage = course.attendance_percentage || 0;

      // FIXED: Use the actual percentage from backend instead of recalculating
      const displayPercentage = attendancePercentage;

      courseDiv.innerHTML = `
        <div class="course-header">
          <h4>${course.course_code}</h4>
          <span class="course-total">${presentCount}/${enrolledCount} students (${displayPercentage}%)</span>
        </div>
        <div class="pie-chart-container">
          <canvas id="courseChart${index}" width="150" height="150"></canvas>
        </div>
        <div class="course-stats">
          <div class="stat-item present">
            <span class="stat-dot present-dot"></span>
            <span>Present: ${presentCount}</span>
          </div>
          <div class="stat-item absent">
            <span class="stat-dot absent-dot"></span>
            <span>Absent: ${absentCount}</span>
          </div>
        </div>
      `;

      container.appendChild(courseDiv);

      // Create the chart with a small delay to ensure DOM is ready
      setTimeout(() => {
        const ctx = document.getElementById(`courseChart${index}`);
        if (!ctx) {
          console.error(`Canvas element courseChart${index} not found`);
          return;
        }

        try {
          const chart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
              labels: ['Present', 'Absent'],
              datasets: [{
                data: [presentCount, absentCount],
                backgroundColor: ['#2ECC71', '#e74c3c'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverBackgroundColor: ['#27AE60', '#c0392b'],
                hoverBorderWidth: 3
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: 'rgba(44, 62, 80, 0.95)',
                  titleColor: '#ffffff',
                  bodyColor: '#ffffff',
                  borderColor: '#3498db',
                  borderWidth: 2,
                  cornerRadius: 8,
                  displayColors: true,
                  callbacks: {
                    title: function(context) {
                      return course.course_code;
                    },
                    label: function(context) {
                      const label = context.label || '';
                      const value = context.parsed;
                      const total = enrolledCount;
                      const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                      return `${label}: ${value} students (${percentage}%)`;
                    },
                    afterBody: function(context) {
                      return [`Total Enrolled: ${enrolledCount}`, `Attendance Rate: ${displayPercentage}%`];
                    }
                  }
                }
              },
              cutout: '60%',
              animation: {
                animateRotate: true,
                animateScale: false,
                duration: 1000
              }
            }
          });

          courseCharts.push(chart);
          console.log(`Chart created successfully for ${course.course_code}`);

        } catch (error) {
          console.error(`Error creating chart for ${course.course_code}:`, error);

          // Show error in the chart container
          const chartContainer = document.getElementById(`courseChart${index}`);
          if (chartContainer) {
            chartContainer.parentElement.innerHTML = `
              <div class="chart-error">
                <i class="fas fa-exclamation-triangle"></i>
                <small>Error loading chart</small>
              </div>
            `;
          }
        }
      }, 100 * index); // Stagger chart creation
    });

    console.log(`Created ${courseData.length} course charts successfully`);
  })
  .catch(error => {
    console.error('Error loading course attendance data:', error);

    container.innerHTML = `
      <div class="no-data" style="grid-column: 1 / -1;">
        <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
        <p><strong>Error loading course attendance data</strong></p>
        <small>Error: ${error.message}</small>
        <div style="margin-top: 15px;">
          <button onclick="initializeCourseCharts()"
                  class="btn btn-sm"
                  style="padding: 8px 16px; border: none; border-radius: 8px;
                         background: #3498db; color: white; cursor: pointer; font-weight: 600;">
            Retry
          </button>
        </div>
      </div>
    `;
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
function refreshDashboardDataUpdated() {
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
      absentPercentage: document.getElementById('absentPercentage'),
      activeCourses: document.getElementById('activeCourses') // Add this line
    };

    if (elements.totalStudents) elements.totalStudents.textContent = data.totalStudents;
    if (elements.presentToday) elements.presentToday.textContent = data.present;
    if (elements.absentToday) elements.absentToday.textContent = data.absent;
    if (elements.presentPercentage) elements.presentPercentage.textContent = data.presentPercentage + '%';
    if (elements.absentPercentage) elements.absentPercentage.textContent = data.absentPercentage + '%';

    // Update active courses count
    if (elements.activeCourses) {
      elements.activeCourses.textContent = data.activeCourses || 0;
    }

    // Update time-based stats (keep existing late and early counts)
    if (data.timeBasedStats) {
      const timeElements = {
        lateCount: document.getElementById('lateCount'),
        earlyCount: document.getElementById('earlyCount')
      };

      if (timeElements.lateCount) timeElements.lateCount.textContent = data.timeBasedStats.late;
      if (timeElements.earlyCount) timeElements.earlyCount.textContent = data.timeBasedStats.early;
    }
  })
  .catch(error => console.error('Error refreshing data:', error));
}

// Initialize active courses count on page load
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(updateActiveCourses, 500);
});

function refreshCourseData() {
  const refreshIcon = document.getElementById('courseRefreshIcon');
  if (refreshIcon) {
    refreshIcon.classList.add('spinning');
  }

  console.log('Refreshing course data...');
  initializeCourseCharts();

  // Remove spinning after delay
  setTimeout(() => {
    if (refreshIcon) {
      refreshIcon.classList.remove('spinning');
    }
  }, 2500);
}

// Enhanced refresh function with better UX
function refreshEnrollmentChart() {
  const refreshIcon = document.getElementById('enrollmentRefreshIcon');
  const refreshBtn = refreshIcon?.parentElement;

  if (refreshIcon) {
    refreshIcon.classList.add('spinning');
  }

  if (refreshBtn) {
    refreshBtn.style.pointerEvents = 'none';
    refreshBtn.style.opacity = '0.7';
  }

  initializeEnrollmentChart();

  setTimeout(() => {
    if (refreshIcon) {
      refreshIcon.classList.remove('spinning');
    }
    if (refreshBtn) {
      refreshBtn.style.pointerEvents = 'auto';
      refreshBtn.style.opacity = '1';
    }
  }, 2500);
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Start clock immediately
  updateTime();
  setInterval(updateTime, 1000);

  // Initialize charts with delays to prevent conflicts
  setTimeout(initializeEnrollmentChart, 500);
  setTimeout(initializeCourseCharts, 1000);
  setTimeout(initializeWeeklyChart, 1500);
  setTimeout(initializeHeatmap, 2000);
  setTimeout(initializeTopCourses, 2500);

  // Set up auto-refresh intervals
  setInterval(refreshDashboardData, 30000); // Every 30 seconds
  setInterval(() => {
    initializeHeatmap();
    initializeTopCourses();
  }, 60000); // Every minute
  setInterval(initializeCourseCharts, 45000); // Every 45 seconds for courses
  setInterval(initializeEnrollmentChart, 60000); // Every minute for enrollments
});

// Handle window resize
window.addEventListener('resize', function() {
  [enrollmentChart, topCoursesChart, weeklyChart, ...courseCharts].forEach(chart => {
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






