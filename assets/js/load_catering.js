document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("loadMoreVilla")) {
    const loadMoreButton = document.getElementById("loadMoreVilla");
    let offset = 4;
    let limit = 4;
    let page = 2;

    loadMoreButton.addEventListener("click", function () {
      loadMoreButton.innerText = "Loading...";
      loadMoreButton.disabled = true;

      fetch("../includes/database-user/load_catering.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `category=${category}&offset=${offset}&limit=${limit}&page=${page}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success" && data.data.html !== "") {
            console.log("s");
            document.getElementById("villa").innerHTML += data.data.html;
            offset += limit;
            page++;
          } else {
            console.log(data);
          }
          if (data.data.isLastPage) {
            loadMoreButton.style.display = "none";
          }
          loadMoreButton.innerText = "Load More";
          loadMoreButton.disabled = false;
        })
        .catch((error) => console.error("Error:", error));
    });
  }
});
