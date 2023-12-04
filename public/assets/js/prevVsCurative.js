// Setup block
let data = {
  labels: "",
  datasets: [
    {
      label: "Préventif",
      data: [],
      backgroundColor: ["#E62A00"],
      borderColor: ["#E62A00"],
      borderWidth: 1,
    },
    {
      label: "Curatif",
      data: [],
      backgroundColor: ["#420BE6"],
      borderColor: ["#420BE6"],
      borderWidth: 1,
    },
  ],
};

// Config block
const config = {
  type: "bar",
  data,
  options: {
    responsive: true,
    elements: {},
    scales: {
      x: {
        title: {
          color: "black",
          display: true,
          text: "Mois",
          font: {
            size: 15,
            family: "tahoma",
            weight: "bold",
          },
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
          text: "Temps(minutes)",
          font: {
            size: 15,
            family: "tahoma",
            weight: "bold",
          },
        },
        grid: {
          color: "blue",
        },
        ticks: {
          // Include a dollar sign in the ticks
          callback: function (value, index, ticks) {
            return value + " min";
          },
        },
        beginAtZero: false,
      },
    },
    plugins: {
      subtitle: {
        display: true,
        text: "Préventif VS Curatif",
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
  try {
    const datesDiv = document.getElementById("chartLabels");
    let dates = JSON.parse(datesDiv.dataset.chartdates);
    data.labels = dates;
  } catch (error) {
    console.log(error);
  }

  try {
    const valuesDiv = document.getElementById("chartData1");
    let valuesPreventive = JSON.parse(valuesDiv.dataset.dataset1);
    data.datasets[0].data = valuesPreventive;
  } catch (error) {
    console.log(error);
  }

  try {
    const valuesDiv = document.getElementById("chartData2");
    let valuesCurative = JSON.parse(valuesDiv.dataset.dataset2);
    data.datasets[1].data = valuesCurative;
  } catch (error) {
    console.log(error);
  }

  try {
    const myChart = new Chart(document.getElementById("myChart"), config);
    let dateNotOk = document.createElement("p");
    document.getElementById("graphique").prepend(dateNotOk);
  } catch (error) {
    dateNotOk.innerText = "Il n'y a pas de données pour ces dates";
    dateNotOk.className = "no-data";
  }
});
