function (commit) {
    if (/^testing\.blog\.article\-/.test(commit._id)) {
        emit([ commit.streamId, commit.streamRevision ], 1);
    }
}