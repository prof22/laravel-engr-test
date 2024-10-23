<template>
    <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
      <div class="order-form container mt-5 p-4 shadow-sm bg-light rounded">
        <h2 class="text-center mb-4">Submit New Order</h2>
           <!-- Flash Message -->
      <div v-if="flashMessage" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ flashMessage }}
        <button type="button" class="btn-close" @click="flashMessage = ''" aria-label="Close"></button>
      </div>

        <!-- Provider Info -->
        <div class="provider-info mb-3">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="hmo_code" class="form-label">HMO Code:</label>
              <input
                type="text"
                id="hmo_code"
                v-model="order.hmo_code"
                placeholder="Enter HMO Code"
                class="form-control"
              />
            </div>
            <div class="col-md-6">
              <label for="provider_name" class="form-label">Provider Name:</label>
              <input
                type="text"
                id="provider_name"
                v-model="order.provider_name"
                placeholder="Enter Provider Name"
                class="form-control"
              />
            </div>
          </div>
        </div>
  
        <!-- Encounter Date -->
        <div class="date-field mb-3">
          <label for="encounter_date" class="form-label">Encounter Date:</label>
          <input type="date" id="encounter_date" v-model="order.encounter_date" class="form-control" />
        </div>
  
        <!-- Order Items -->
        <div class="order-items mb-4">
          <h3 class="mb-3">Order Items</h3>
  
          <div v-for="(item, index) in order.items" :key="index" class="item-row row align-items-center mb-2">
            <div class="col-md-2">
              <label for="item-name">Item Name:</label>
              <input
                type="text"
                v-model="item.name"
                placeholder="Item Name"
                class="form-control"
                id="item-name"
              />
            </div>
  
            <div class="col-md-2">
              <label for="unit-price">Unit Price:</label>
              <input
                type="number"
                v-model="item.unit_price"
                @input="calculateSubtotal(item)"
                placeholder="Unit Price"
                class="form-control"
                id="unit-price"
              />
            </div>
  
            <div class="col-md-4 d-flex align-items-center">
              <label class="me-2" for="quantity">Quantity:</label>
              <button class="btn btn-outline-secondary mx-1" @click="decreaseQty(item)">-</button>
              <input
                type="number"
                v-model="item.quantity"
                @input="calculateSubtotal(item)"
                class="form-control text-center"
                style="width: 50px;"
                id="quantity"
              />
              <button class="btn btn-outline-secondary mx-1" @click="increaseQty(item)">+</button>
            </div>
  
            <div class="col-md-2">
              <label for="subtotal">Subtotal:</label>
              <input type="number" v-model="item.subtotal" readonly class="form-control" id="subtotal" />
            </div>
  
            <div class="col-md-1">
              <button class="btn btn-danger" @click="removeItem(index)">Remove</button>
            </div>
          </div>
  
          <button class="btn btn-primary mt-3" @click="addItem">Add Item</button>
        </div>
  
        <!-- Total Amount -->
        <div class="total-amount text-end">
          <h3>Total: {{ calculateTotal() }}</h3>
        </div>
  
        <!-- Submit Button with Loader -->
        <div class="text-center mt-4">
          <button class="btn btn-success btn-lg" @click="submitOrder" :disabled="loading">
            <span v-if="!loading">Submit Order</span>
            <span v-else>
              <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </span>
          </button>
        </div>
  
        <!-- Modal for Success Alert -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Order Submitted</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                Order has been successfully submitted!
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Table to Display Order Items -->
        <div class="mt-5">
  <h3 class="text-center mb-4">Submitted Order Items</h3>
  <div class="container mt-4">
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Provider Name</th>
            <th>Item Name</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody id="ordersTableBody">
          <!-- Loop through orders -->
          <template v-for="(order, orderIndex) in submittedOrders" :key="orderIndex">
            <!-- Loop through items for each order -->
            <tr v-for="(item, itemIndex) in order.items" :key="itemIndex">
              <!-- Display provider_name only on the first item row for the order -->
              <td v-if="itemIndex === 0" :rowspan="order.items.length">{{ order.provider_name }}</td>

              <!-- Display item details -->
              <td>{{ item.item_name }}</td>
              <td>{{ item.unit_price }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ item.subtotal }}</td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</div>


      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        loading: false,
        order: {
          hmo_code: '',
          provider_name: '',
          encounter_date: '',
          items: [{ name: '', unit_price: 0, quantity: 1, subtotal: 0 }],
        },
        submittedOrders: [], // To store submitted orders
        flashMessage: '', // For flash message
      };
    },
    methods: {
      fetchOrders() {
        // API call to fetch submitted orders
        fetch('/api/orders')
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              this.submittedOrders = data.data; // Set submitted orders directly
            } else {
              console.error('Error: Data not found');
            }
          })
          .catch((error) => {
            console.error('Error fetching orders:', error);
          });
      },
      addItem() {
        this.order.items.push({ name: '', unit_price: 0, quantity: 1, subtotal: 0 });
      },
      removeItem(index) {
        this.order.items.splice(index, 1);
      },
      increaseQty(item) {
        item.quantity++;
        this.calculateSubtotal(item);
      },
      decreaseQty(item) {
        if (item.quantity > 1) {
          item.quantity--;
          this.calculateSubtotal(item);
        }
      },
      calculateSubtotal(item) {
        item.subtotal = item.unit_price * item.quantity;
      },
      calculateTotal() {
        return this.order.items.reduce((total, item) => total + item.subtotal, 0);
      },
      submitOrder() {
  this.loading = true;
  const payload = {
    ...this.order,
    total_amount: this.calculateTotal(),
  };

  fetch('/api/orders', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update the submittedOrders to trigger reactivity
        this.submittedOrders = [...this.submittedOrders, data.order];

        this.clearForm(); // Clear the form

         // Set flash message
            this.flashMessage = 'Order has been successfully submitted!';

            // Automatically hide the flash message after a few seconds
            setTimeout(() => {
              this.flashMessage = '';
            }, 3000);

      } else {
        console.error('Error: Order submission failed.');
      }

      this.loading = false;
    })
    .catch((error) => {
      console.error('Error submitting order:', error);
      this.loading = false;
    });
},
      clearForm() {
        // Clear all fields
        this.order = {
          hmo_code: '',
          provider_name: '',
          encounter_date: '',
          items: [{ name: '', unit_price: 0, quantity: 1, subtotal: 0 }],
        };
      },
    },
    mounted() {
      // Fetch orders when the component is mounted
      this.fetchOrders();
    },
  };
  </script>
  <style scoped>
  /* Styling for the alert box */
.alert {
  position: relative;
  z-index: 1000; /* Ensure it's on top */
}
  /* Styling to fix subtotal overlap issue */
  .item-row label {
    font-size: 14px;
  }
  
  input[type="number"] {
    width: 100px;
  }
  
  .order-form {
    max-width: 800px;
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
  }
  
  .order-items .item-row {
    display: flex;
    align-items: center;
  }
  
  .spinner-border {
    width: 1rem;
    height: 1rem;
  }
  </style>
  