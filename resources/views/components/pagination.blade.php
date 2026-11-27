
<div class="pagination">

    <div class="pagination-summary">
        <span>Show</span>

        <select
            id="{{ $idPrefix }}PerPage"
            class="pagination-select"
            aria-label="Records per page"
        >
            <option value="1">1</option>
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
        </select>

        <span>Records</span>
    </div>

    <div class="pagination-buttons">
        <button type="button" id="{{ $idPrefix }}PreviousBtn" disabled>
            Previous
        </button>

        <span id="{{ $idPrefix }}PageInfo">Page 1 of 1</span>

        <button type="button" id="{{ $idPrefix }}NextBtn" disabled>
            Next
        </button>
    </div>

</div>
