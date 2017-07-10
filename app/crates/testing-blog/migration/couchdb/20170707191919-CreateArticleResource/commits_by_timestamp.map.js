function (commit) {
    if (/^testing\.blog\.article\-/.test(commit._id) && commit.streamRevision) {
        emit(commit.iso_date, 1);
    }
}