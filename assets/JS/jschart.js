// Setup block
let data = {
  labels: "",
  datasets: [
    {
      label: "Evolution stock",
      data: [],
      backgroundColor: ["rgba(255, 159, 64, 0.2)"],
      borderColor: ["rgba(255, 99, 132, 1)"],
      borderWidth: 1,
    },
  ],
};

// Config block
const config = {
  type: "line",
  data,
  options: {
    responsive: true,
    elements: {
      line: {
        tension: 0.3,
      },
    },
    scales: {
      x: {
        title: {
          color: "black",
          display: true,
          text: "Dates",
        },
        grid: {
          color: "blue",
        },
        ticks: {
          color: "red",
          display: true,
        },
      },
      y: {
        title: {
          color: "black",
          display: true,
          text: "Montant",
        },
        grid: {
          color: "blue",
        },
        ticks: {
          // Include a dollar sign in the ticks
          callback: function (value, index, ticks) {
            return value + " €";
          },
        },
        beginAtZero: true,
      },
    },
    plugins: {
      subtitle: {
        display: true,
        text: "Evolution stock",
        color: "blue",
        font: {
          size: 20,
          family: "tahoma",
          weight: "bold",
        },
        padding: {
          bottom: 20,
        },
      },
    },
  },
};

// Render / Init block
document.addEventListener("DOMContentLoaded", function () {
  let datesDiv = document.querySelector(".date");
  let dates = datesDiv.dataset.chartdates;
  let datesValues = JSON.parse(dates);
  data.labels = datesValues;

  let amountsDiv = document.querySelector(".amount");
  let amounts = amountsDiv.dataset.chartamounts;
  let amountsValues = JSON.parse(amounts);
  data.datasets[0].data = amountsValues;

  const myChart = new Chart(document.getElementById("myChart"), config);
});
