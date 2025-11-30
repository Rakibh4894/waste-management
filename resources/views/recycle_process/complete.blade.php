@extends('website.master')

@section('title', 'Complete Recycling')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Form Card --}}
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="card-title mb-0">Complete Recycling</h5></div>

            <div class="card-body">
                <form method="POST" action="{{ route('recycle-process.completeUpdate', $id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                       {{-- Recyclable Items Grid --}}
                        <div class="col-md-12 mb-4">
                            <label class="form-label fw-bold">Recyclable Items</label>

                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Item Type</th>
                                        <th width="25%">Item Name</th>
                                        <th width="8%">Weight (kg)</th>
                                        <th width="8%">Qty</th>
                                        <th width="8%">Probable Value</th>
                                        <th width="13%">Note</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select id="itemType" class="form-select">
                                                <option value="household">Household</option>
                                                <option value="plastic">Plastic</option>
                                                <option value="medical">Medical</option>
                                                <option value="construction">Construction</option>
                                                <option value="organic">Organic</option>
                                                <option value="industrial">Industrial</option>
                                                <option value="e-waste">E-Waste</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="itemName">
                                        </td>
                                        <td>
                                            <input type="text" id="quantity" class="form-control" >
                                        </td>
                                        <td>
                                            <input type="text" id="weight" class="form-control" >
                                        </td>
                                        <td>
                                            <input type="text" id="value" class="form-control" >
                                        </td>
                                        <td>
                                            <input type="text" id="note" class="form-control" >
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm" onclick="addNew">Add</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-12 mb-4">    
                          <table id="gridItems" class="table table-bordered" id="itemsTable">

                            <thead class="table-light">
                                <tr>
                                
                                    <th width="3%">SL</th>
                                    <th width="15%">Item Type</th>
                                    <th width="18%">Item Name</th>
                                    <th width="8%">Weight (kg)</th>
                                    <th width="8%">Qty</th>
                                    <th width="8%">Probable Value</th>
                                    <th width="13%">Note</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>

                            <tbody>

                            </tbody>

                            </table>
                        </div>


                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">Mark Complete</button>
                            <a href="{{ route('recycle-process.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
@section('footer_js')
<script>
let rowCount = 0;

function addNew() {
    // Get values
    let itemType  = document.getElementById('itemType').value.trim();
    let itemName  = document.getElementById('itemName').value.trim();
    let weight    = document.getElementById('weight').value.trim();
    let quantity  = document.getElementById('quantity').value.trim();
    let value     = document.getElementById('value').value.trim();
    let note      = document.getElementById('note').value.trim();

    // Simple validation
    if(!itemType || !itemName || !weight || !quantity){
        alert("Please fill all required fields: Item Type, Name, Weight, Qty");
        return;
    }

    rowCount++;

    // Create row
    let tbody = document.querySelector('#gridItems tbody');
    let row = document.createElement('tr');

    row.innerHTML = `
        <td class="text-center serial"></td>
        <td>
            <input type="hidden" name="item_type[]" value="${itemType}">
            ${itemType}
        </td>
        <td>
            <input type="hidden" name="item_name[]" value="${itemName}">
            ${itemName}
        </td>
        <td>
            <input type="hidden" name="weight[]" value="${weight}">
            ${weight}
        </td>
        <td>
            <input type="hidden" name="quantity[]" value="${quantity}">
            ${quantity}
        </td>
        <td>
            <input type="hidden" name="value[]" value="${value}">
            ${value}
        </td>
        <td>
            <input type="hidden" name="note[]" value="${note}">
            ${note}
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button>
        </td>
    `;

    tbody.appendChild(row);

    // Recalculate serials
    updateSerials();

    // Clear input fields
    document.getElementById('itemName').value = '';
    document.getElementById('weight').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('value').value = '';
    document.getElementById('note').value = '';
}

// Remove a row
function removeRow(button) {
    button.closest('tr').remove();
    updateSerials();
    rowCount--;
}

// Update serial numbers
function updateSerials() {
    let rows = document.querySelectorAll('#gridItems tbody tr');
    rows.forEach((row, index) => {
        row.querySelector('.serial').innerText = index + 1;
    });
}

// Attach addNew function to the Add button
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('#itemsTable button').addEventListener('click', addNew);

    // Form submit check
    document.querySelector('form').addEventListener('submit', function(e){
        let totalRows = document.querySelectorAll('#gridItems tbody tr').length;
        if(totalRows === 0){
            e.preventDefault(); // Stop form submission
            alert("Please add at least one recyclable item before submitting.");
        }
    });
});
</script>
@endsection