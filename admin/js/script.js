window.onload = () => {
  const wp_nonce = document.getElementById("fcbjc_wp_nonce").value;
  const api_url = document.getElementById("fcbjc_host_url").value;
  const fcbjc_unreaded_lead_table = document.getElementById(
    "fcbjc_unreaded_lead_table"
  );
  const fcbjc_unreaded_lead_table_error = document.getElementById(
    "fcbjc_unreaded_lead_table_error"
  );
  const fcbjc_readed_lead_table = document.getElementById(
    "fcbjc_readed_lead_table"
  );
  const fcbjc_readed_lead_table_error = document.getElementById(
    "fcbjc_readed_lead_table_error"
  );

  async function mark_readed(lead_id, row) {
    row.classList.add("fcbjc_loading");
    const response = await fetch(
      `${api_url}/?rest_route=/fcbjc/v1/lead/mark_readed/${lead_id}`,
      {
        method: "POST",
        credentials: "same-origin",
        mode: "cors",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-WP-Nonce": wp_nonce,
        },
      }
    );
    row.classList.remove("fcbjc_loading");
    if (response.ok) {
      const lead = await response.json();
      fcbjc_unreaded_lead_table.removeChild(row);
      fcbjc_readed_lead_table.appendChild(new_readed_lead_child(lead));
      return;
    }
    fcbjc_unreaded_lead_table_error.style.display = "block";
  }

  async function mark_unreaded(lead_id, row) {
    row.classList.add("fcbjc_loading");
    const response = await fetch(
      `${api_url}/?rest_route=/fcbjc/v1/lead/mark_unreaded/${lead_id}`,
      {
        method: "POST",
        credentials: "same-origin",
        mode: "cors",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-WP-Nonce": wp_nonce,
        },
      }
    );
    row.classList.remove("fcbjc_loading");
    if (response.ok) {
      const lead = await response.json();
      fcbjc_readed_lead_table.removeChild(row);
      fcbjc_unreaded_lead_table.appendChild(new_unreaded_lead_child(lead));
      return;
    }
    fcbjc_readed_lead_table_error.style.display = "block";
  }

  async function get_readed_leads() {
    fcbjc_readed_lead_table.classList.add("fcbjc_loading");
    const response = await fetch(
      `${api_url}/?rest_route=/fcbjc/v1/lead/readed`,
      {
        method: "GET",
        credentials: "same-origin",
        mode: "cors",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-WP-Nonce": wp_nonce,
        },
      }
    );
    fcbjc_readed_lead_table.classList.remove("fcbjc_loading");
    if (response.ok) {
      const json = await response.json();
      json.forEach((lead) => {
        fcbjc_readed_lead_table.appendChild(new_readed_lead_child(lead));
      });
      return;
    }
    fcbjc_readed_lead_table_error.style.display = "block";
  }

  async function get_unreaded_leads() {
    fcbjc_unreaded_lead_table.classList.add("fcbjc_loading");
    const response = await fetch(
      `${api_url}/?rest_route=/fcbjc/v1/lead/unreaded`,
      {
        method: "GET",
        credentials: "same-origin",
        mode: "cors",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-WP-Nonce": wp_nonce,
        },
      }
    );
    fcbjc_unreaded_lead_table.classList.remove("fcbjc_loading");
    if (response.ok) {
      const json = await response.json();
      json.forEach((lead) => {
        fcbjc_unreaded_lead_table.appendChild(new_unreaded_lead_child(lead));
      });
      return;
    }
    fcbjc_unreaded_lead_table_error.style.display = "block";
  }

  function new_readed_lead_child(lead) {
    const leadChild = document.createElement("tr");
    const id = document.createElement("td");
    id.innerHTML = lead.id;
    leadChild.appendChild(id);
    const name = document.createElement("td");
    name.innerHTML = lead.name;
    leadChild.appendChild(name);
    const email = document.createElement("td");
    email.innerHTML = lead.email;
    leadChild.appendChild(email);
    const message = document.createElement("td");
    message.classList.add("fcbjc_lead_table_message");
    message.innerHTML = lead.message;
    leadChild.appendChild(message);
    const created_at = document.createElement("td");
    created_at.classList.add("fcbjc_lead_table_date");
    created_at.innerHTML = lead.created_at;
    leadChild.appendChild(created_at);
    const readed_at = document.createElement("td");
    readed_at.classList.add("fcbjc_lead_table_date");
    readed_at.innerHTML = lead.readed_at;
    leadChild.appendChild(readed_at);
    const actions = document.createElement("td");
    const unreaded = document.createElement("button");
    unreaded.innerHTML = "Unread";
    actions.appendChild(unreaded);
    leadChild.appendChild(actions);
    unreaded.addEventListener("click", () => mark_unreaded(lead.id, leadChild));
    return leadChild;
  }

  function new_unreaded_lead_child(lead) {
    const leadChild = document.createElement("tr");
    const id = document.createElement("td");
    id.innerHTML = lead.id;
    leadChild.appendChild(id);
    const name = document.createElement("td");
    name.innerHTML = lead.name;
    leadChild.appendChild(name);
    const email = document.createElement("td");
    email.innerHTML = lead.email;
    leadChild.appendChild(email);
    const message = document.createElement("td");
    message.classList.add("fcbjc_lead_table_message");
    message.innerHTML = lead.message;
    leadChild.appendChild(message);
    const created_at = document.createElement("td");
    created_at.classList.add("fcbjc_lead_table_date");
    created_at.innerHTML = lead.created_at;
    leadChild.appendChild(created_at);
    const actions = document.createElement("td");
    const readed = document.createElement("button");
    readed.innerHTML = "Readed";
    actions.appendChild(readed);
    leadChild.appendChild(actions);
    readed.addEventListener("click", () => mark_readed(lead.id, leadChild));
    return leadChild;
  }

  get_readed_leads();
  get_unreaded_leads();
};
