SMD - Smart Manufacturing Dashboard

## Overview

The Smart Manufacturing Dashboard project offers a unique opportunity for a local manufacturer to adopt Industry 4.0 techniques and transform it into a smart factory. By the system, operators, managers, and administrators will have insight visually into the operations of several machines in their facility; this will allow them to further understand the factory functionality with data in real-time.

## Features

- **User Roles and Privileges**: The dashboard supports four different user roles:
    - **Administrators**: Managing user accounts and roles and controlling view access to the dashboard
    - **Factory Managers**: Overall performance monitoring of a factory, machine, and job management and assigning roles to Production Operators
    - **Production Operators:** Factory performance monitoring, updating data for machines and jobs allotted to them, and creating task notes for Factory Managers
    - **Auditors**: View access to the dashboard and reports in summary forms for particular dates.

-**CRUD Operations**: He will be able to create, read, update, and delete various elements of machines, jobs, and maintenance requests.

-**Interaction with Data**: See detailed machine performance, drill down into historical log data for machines, and receive notifications about the status of machinery.

-**Intuitive Interface**: Since users today lack technical competencies, this dashboard will be installed on a touch screen device. Ease of use is an important aspect in this factory environment.

## Expectations of Prototype

This prototype will use log data provided-so-called `factory_logs.csv`-historical data produced by the machines running in the factory-which will be used to demonstrate an interactive dashboard reflecting changes to the states of the machines with respect to time.

### User Requirements

**CEO**:
- Smashing of factory performance into one visualization.
- Big picture of overall factory operations on one screen; zoom in on specific machines.
- Instant notification of machine downtimes for immediate action decisions.

**Auditor**: 
- Immediate insight into the factory's performance for government requirements. 
- All historical data can be viewed in tabular format over a given date range.

**Factory Manager**: 
- Easily assign jobs to production operators.
- Role management, depending on the availability of the staff on production.
- Initial setup of parameters for new machines.

**Production Operator:**
- Friendly and easy-to-use interface for multi-job management.
- Notifications concerning important updates that may arise.
- Large buttons, light to click in order to enhance usability on the factory floor.

## Installation

1. Clone this repository:
   ```bash
   git clone https://github.com/KazzzzzyBazzzzzy/COMP-2030---Blue-Dolphins.git