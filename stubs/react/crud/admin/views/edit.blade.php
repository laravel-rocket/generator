import React from "react";

import AdminUserRepository from "../../repositories/{{ $modelName }}Repository";
import columns from './_columns'
import info from "./_info";
import {withRouter} from 'react-router-dom'
import Edit from "../CRUDBase/Edit";

class {{ $modelName }}Edit extends Edit {

  setPageInfo() {
    this.title = info.title;
    this.path = info.path;
  }

  setRepository() {
    this.repository = new {{ $modelName }}rRepository();
  }

  setColumnInfo() {
    this.columns = columns;
  }
}

export default withRouter({{ $modelName }}Edit);
